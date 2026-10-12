<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\Consultation;
use App\Models\Diagnosis;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * OPD consultations with versioned clinical notes (Part B: corrections create a
 * new version with a reason; nothing is hard-deleted).
 */
class ConsultationService
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    /**
     * Save or replace the active consultation for an encounter.
     *
     * @param  array<string, mixed>  $data
     */
    public function save(Encounter $encounter, array $data, User $user, ?string $correctionReason = null): Consultation
    {
        return DB::transaction(function () use ($encounter, $data, $user, $correctionReason) {
            $active = $encounter->consultations()
                ->whereNull('voided_at')
                ->latest('id')
                ->first();

            $nextVersion = 1;
            if ($active) {
                $nextVersion = (int) $active->version + 1;
                $active->forceFill(['voided_at' => now(), 'void_reason' => $correctionReason ?: 'superseded'])->save();
            }

            $consultation = $encounter->consultations()->create([
                'facility_id' => $encounter->facility_id,
                'patient_id' => $encounter->patient_id,
                'history' => $data['history'] ?? null,
                'examination' => $data['examination'] ?? null,
                'plan' => $data['plan'] ?? null,
                'disposition' => $data['disposition'] ?? null,
                'follow_up_date' => $data['follow_up_date'] ?? null,
                'version' => $nextVersion,
                'previous_version_id' => $active?->getKey(),
                'created_by' => $user->getKey(),
                'signed_at' => $data['signed'] ?? false ? now() : null,
            ]);

            $this->audit->log(AuditAction::Create, $consultation, $encounter->patient, 'Consultation saved', [], [
                'version' => $nextVersion,
                'corrected' => $active !== null,
            ], $user);

            return $consultation;
        });
    }

    /**
     * Replace the diagnoses of a consultation.
     *
     * @param  array<int, array<string, mixed>>  $diagnoses
     */
    public function syncDiagnoses(Consultation $consultation, array $diagnoses, User $user): void
    {
        DB::transaction(function () use ($consultation, $diagnoses, $user) {
            $consultation->diagnoses()->whereNull('voided_at')->get()->each(function (Diagnosis $diagnosis) {
                $diagnosis->forceFill(['voided_at' => now(), 'void_reason' => 'superseded'])->save();
            });

            foreach ($diagnoses as $index => $row) {
                if (empty($row['icd10_code']) && empty($row['description'])) {
                    continue;
                }

                Diagnosis::create([
                    'facility_id' => $consultation->facility_id,
                    'patient_id' => $consultation->patient_id,
                    'encounter_id' => $consultation->encounter_id,
                    'consultation_id' => $consultation->getKey(),
                    'icd10_code' => $row['icd10_code'] ?? null,
                    'icd10_description' => $row['icd10_description'] ?? ($row['description'] ?? null),
                    'icd10_code_id' => $row['icd10_code_id'] ?? null,
                    'type' => $row['type'] ?? ($index === 0 ? 'primary' : 'secondary'),
                    'notes' => $row['notes'] ?? null,
                    'diagnosed_by' => $user->getKey(),
                    'diagnosed_at' => now(),
                ]);
            }

            $this->audit->log(AuditAction::Update, $consultation, $consultation->patient, 'Diagnoses updated', [], [
                'count' => count($diagnoses),
            ], $user);
        });
    }
}