<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Http\Requests\SaveTriageRequest;
use App\Models\Encounter;
use App\Models\Queue;
use App\Models\QueueEntry;
use App\Models\VitalSign;
use App\Services\AuditLogger;
use App\Services\EncounterService;
use Illuminate\Http\Request;

class TriageController extends Controller
{
    public function __construct(
        private readonly EncounterService $encounters,
        private readonly AuditLogger $audit,
    ) {
    }

    /**
     * Triage nurse waitlist (Part D7.2).
     */
    public function index()
    {
        $this->authorize('viewAny', \App\Models\Patient::class);

        $queue = Queue::where('type', 'triage')->with('department')->get();

        $waiting = QueueEntry::with(['patient', 'queue'])
            ->whereIn('queue_id', $queue->pluck('id'))
            ->waiting()
            ->orderBy('priority')
            ->orderBy('added_at')
            ->get();

        return view('triage.index', [
            'queues' => $queue,
            'waiting' => $waiting,
        ]);
    }

    public function create(Encounter $encounter, Request $request)
    {
        $this->authorize('view', $encounter);

        return view('triage.form', [
            'encounter' => $encounter->load('patient', 'latestVitals'),
        ]);
    }

    public function store(SaveTriageRequest $request)
    {
        $encounter = Encounter::findOrFail($request->input('encounter_id'));
        $this->authorize('update', $encounter);

        $vitalsData = $request->input('vitals', []);
        $latest = $encounter->latestVitals;
        $previousWeight = $latest?->weight_kg;
        $previousHeight = $latest?->height_cm;

        if ($vitalsData !== []) {
            $vitals = VitalSign::create([
                'facility_id' => $encounter->facility_id,
                'patient_id' => $encounter->patient_id,
                'encounter_id' => $encounter->getKey(),
                'temperature_c' => $vitalsData['temperature_c'] ?? null,
                'pulse' => $vitalsData['pulse'] ?? null,
                'respiratory_rate' => $vitalsData['respiratory_rate'] ?? null,
                'systolic' => $vitalsData['systolic'] ?? null,
                'diastolic' => $vitalsData['diastolic'] ?? null,
                'spo2' => $vitalsData['spo2'] ?? null,
                'weight_kg' => $vitalsData['weight_kg'] ?? null,
                'height_cm' => $vitalsData['height_cm'] ?? null,
                'muac_mm' => $vitalsData['muac_mm'] ?? null,
                'pain_score' => $vitalsData['pain_score'] ?? null,
                'gcs' => $vitalsData['gcs'] ?? null,
                'notes' => $vitalsData['notes'] ?? null,
                'recorded_by' => $request->user()->getKey(),
                'recorded_at' => now(),
            ]);

            $this->audit->created($vitals, $encounter->patient);
        } else {
            $vitals = null;
        }

        $triage = $encounter->triageAssessments()->create([
            'facility_id' => $encounter->facility_id,
            'patient_id' => $encounter->patient_id,
            'category' => $request->input('category'),
            'chief_complaint' => $request->input('chief_complaint'),
            'notes' => $request->input('notes'),
            'triaged_by' => $request->user()->getKey(),
            'triaged_at' => now(),
        ]);

        $this->audit->log(AuditAction::Create, $triage, $encounter->patient, 'Triage assessment recorded', [], [
            'category' => $request->input('category'),
        ]);

        $queue = $request->input('queue_id')
            ? Queue::find($request->input('queue_id'))
            : Queue::where('type', 'clinical')->first();

        if ($queue) {
            $this->encounters->addToQueue($encounter, $queue, $triage->category, $request->user());
        }

        return redirect()->route('triage.index')
            ->with('status', __('triage.saved'));
    }
}