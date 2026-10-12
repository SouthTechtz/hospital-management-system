<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\SaveConsultationRequest;
use App\Models\Encounter;
use App\Services\AuditLogger;
use App\Services\ConsultationService;
use App\Services\OrderService;

class ConsultationController extends Controller
{
    public function __construct(
        private readonly ConsultationService $consultations,
        private readonly OrderService $orders,
        private readonly AuditLogger $audit,
    ) {
    }

    /**
     * Single-page OPD consultation (Part D7.6): History, Examination, Diagnosis
     * (ICD-10), Orders, Prescription, Plan with a side panel of previous
     * encounters and active allergy banner.
     */
    public function show(Encounter $encounter)
    {
        $this->authorize('view', $encounter);

        $encounter->load([
            'patient.allergies',
            'patient.facility',
            'department',
            'clinician',
            'triage',
            'consultations' => fn ($q) => $q->with('diagnoses')
                ->whereNull('voided_at')
                ->latest(),
            'diagnoses' => fn ($q) => $q->whereNull('voided_at')->latest(),
            'orders' => fn ($q) => $q->with('items.drug')->whereNull('voided_at')->latest(),
            'invoices' => fn ($q) => $q->latest(),
        ]);

        $previous = $encounter->patient->encounters()
            ->where('encounters.id', '!=', $encounter->getKey())
            ->with(['diagnoses', 'consultations', 'triage'])
            ->latest()
            ->limit(10)
            ->get();

        return view('consultations.show', [
            'encounter' => $encounter,
            'previousEncounters' => $previous,
        ]);
    }

    public function store(Encounter $encounter, SaveConsultationRequest $request)
    {
        $this->authorize('create', [$encounter]);

        $user = $request->user();

        $consultation = $this->consultations->save(
            $encounter,
            $request->validated(),
            $user,
            $request->input('correction_reason'),
        );

        $diagnoses = array_values(array_filter($request->input('diagnoses', []), fn ($row) => ! empty($row['icd10_code']) || ! empty($row['description'])));
        if ($diagnoses !== []) {
            $this->consultations->syncDiagnoses($consultation, $diagnoses, $user);
        }

        $items = array_values(array_filter(
            $request->input('order_items', []),
            fn ($row) => ! empty($row['drug_id']) || ! empty($row['description']),
        ));

        $order = null;
        if ($items !== []) {
            $order = $this->orders->createMedicationOrder(
                $encounter->patient,
                $consultation,
                $items,
                $user,
                [
                    'priority' => $request->input('order_priority', 'routine'),
                    'notes' => $request->input('order_notes'),
                ],
            );
        }

        return redirect()->route('consultations.show', $encounter)
            ->with('status', $order ? __('consultations.saved_with_order') : __('consultations.saved'));
    }
}