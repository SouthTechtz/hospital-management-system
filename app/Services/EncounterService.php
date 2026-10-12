<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\EncounterStatus;
use App\Enums\EncounterType;
use App\Enums\QueueEntryStatus;
use App\Enums\TriageCategory;
use App\Models\CareAssignment;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EncounterService
{
    public function __construct(
        private readonly SequenceService $sequences,
        private readonly AuditLogger $audit,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function open(Patient $patient, array $data, User $user): Encounter
    {
        return DB::transaction(function () use ($patient, $data, $user) {
            $encounter = Encounter::create([
                'facility_id' => $patient->facility_id,
                'patient_id' => $patient->getKey(),
                'visit_number' => $this->nextVisitNumber($patient->facility_id),
                'encounter_type' => $data['encounter_type'] ?? EncounterType::Opd->value,
                'status' => EncounterStatus::Open,
                'department_id' => $data['department_id'] ?? $user->department_id,
                'clinician_id' => $data['clinician_id'] ?? $user->getKey(),
                'location' => $data['location'] ?? null,
                'payment_type' => $data['payment_type'] ?? $patient->payer_type?->value ?? 'cash',
                'attending_encounter_id' => $data['attending_encounter_id'] ?? null,
                'started_at' => now(),
                'created_by' => $user->getKey(),
            ]);

            CareAssignment::firstOrCreate([
                'facility_id' => $patient->facility_id,
                'patient_id' => $patient->getKey(),
                'user_id' => $user->getKey(),
                'released_at' => null,
            ], [
                'department_id' => $encounter->department_id,
                'relationship' => 'responsible',
                'assigned_by' => $user->getKey(),
                'assigned_at' => now(),
            ]);

            $this->audit->log(AuditAction::Create, $encounter, $patient, 'Encounter opened', [], [
                'visit_number' => $encounter->visit_number,
                'encounter_type' => $encounter->encounter_type->value,
            ], $user);

            return $encounter;
        });
    }

    public function close(Encounter $encounter, User $user): void
    {
        $encounter->forceFill([
            'status' => EncounterStatus::Closed,
            'ended_at' => now(),
        ])->save();

        CareAssignment::query()
            ->where('patient_id', $encounter->patient_id)
            ->where('user_id', $user->getKey())
            ->whereNull('released_at')
            ->update(['released_at' => now()]);

        $this->audit->log(AuditAction::Update, $encounter, $encounter->patient, 'Encounter closed', [], [], $user);
    }

    public function addToQueue(Encounter $encounter, Queue $queue, ?TriageCategory $category, User $user): void
    {
        $encounter->queueEntries()->create([
            'facility_id' => $encounter->facility_id,
            'queue_id' => $queue->getKey(),
            'patient_id' => $encounter->patient_id,
            'triage_category' => $category?->value,
            'priority' => $category?->sortOrder() ?? 3,
            'status' => QueueEntryStatus::Waiting,
            'added_at' => now(),
            'added_by' => $user->getKey(),
        ]);
    }

    public function nextVisitNumber(int $facilityId): string
    {
        $value = $this->sequences->next('visit', $facilityId);

        return now()->format('Y').'-'.str_pad((string) $value, 6, '0', STR_PAD_LEFT);
    }
}
