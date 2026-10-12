<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpenEncounterRequest;
use App\Models\Encounter;
use App\Models\Patient;
use App\Services\EncounterService;
use Illuminate\Http\Request;

class EncounterController extends Controller
{
    public function __construct(private readonly EncounterService $encounters)
    {
    }

    public function show(Encounter $encounter, Request $request)
    {
        $this->authorize('view', $encounter);

        app(\App\Services\AuditLogger::class)->patientView($encounter->patient, 'Encounter opened for review');

        $encounter->load([
            'patient', 'department', 'clinician', 'triage',
            'consultations' => fn ($q) => $q->with('diagnoses')->latest(),
            'orders' => fn ($q) => $q->with('items.drug')->latest(),
            'invoices' => fn ($q) => $q->with('items', 'payments')->latest(),
        ]);

        return view('encounters.show', ['encounter' => $encounter]);
    }

    public function store(Patient $patient, OpenEncounterRequest $request)
    {
        $this->authorize('create', Encounter::class);

        $encounter = $this->encounters->open($patient, $request->validated(), $request->user());

        return redirect()->route('encounters.show', $encounter)
            ->with('status', __('encounters.opened'));
    }

    public function close(Encounter $encounter, Request $request)
    {
        $this->authorize('close', $encounter);

        $this->encounters->close($encounter, $request->user());

        return redirect()->route('encounters.show', $encounter)
            ->with('status', __('encounters.closed'));
    }
}