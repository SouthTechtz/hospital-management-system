<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\ConsentPurpose;
use App\Models\CareAssignment;
use App\Models\Patient;
use App\Models\PatientAllergy;
use App\Models\PatientMerge;
use App\Models\PatientNextOfKin;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PatientService
{
    /**
     * Tables that carry patient_id and must follow a merge.
     *
     * @var list<string>
     */
    private const PATIENT_TABLES = [
        'encounters', 'care_assignments', 'patient_allergies', 'consents',
        'diagnoses', 'orders', 'invoices', 'vital_signs', 'triage_assessments',
        'queue_entries', 'consultations', 'exemptions',
    ];

    public function __construct(
        private readonly MrnGenerator $mrn,
        private readonly AuditLogger $audit,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(array $data, User $user): Patient
    {
        return DB::transaction(function () use ($data, $user) {
            $patient = new Patient($data);
            $patient->facility_id ??= $user->facility_id;
            $patient->mrn = $this->mrn->generate($patient->facility_id);
            $patient->registered_by = $user->getKey();
            $patient->registered_at = now();
            $patient->created_by = $user->getKey();
            $patient->updated_by = $user->getKey();
            $patient->save();

            $this->syncNextOfKin($patient, $data['next_of_kin'] ?? []);

            foreach ($data['consents'] ?? [] as $purpose => $granted) {
                if (! $granted) {
                    continue;
                }
                $patient->consents()->create([
                    'facility_id' => $patient->facility_id,
                    'purpose' => $purpose instanceof ConsentPurpose ? $purpose : ConsentPurpose::tryFrom((string) $purpose) ?? ConsentPurpose::Treatment,
                    'granted' => true,
                    'method' => $data['consent_method'] ?? 'verbal',
                    'granted_by' => $user->getKey(),
                    'granted_at' => now(),
                ]);
            }

            // Registering clinician/department takes responsibility for the record.
            CareAssignment::create([
                'facility_id' => $patient->facility_id,
                'patient_id' => $patient->getKey(),
                'user_id' => $user->getKey(),
                'department_id' => $user->department_id,
                'relationship' => 'responsible',
                'assigned_by' => $user->getKey(),
                'assigned_at' => now(),
            ]);

            $this->audit->log(AuditAction::Create, $patient, $patient, 'Patient registered', [], [
                'mrn' => $patient->mrn,
            ], $user);

            return $patient;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Patient $patient, array $data, User $user): Patient
    {
        $before = $patient->getOriginal();

        $patient->fill($data);
        $patient->updated_by = $user->getKey();
        $patient->save();

        if (array_key_exists('next_of_kin', $data)) {
            $this->syncNextOfKin($patient, $data['next_of_kin']);
        }

        if (array_key_exists('consents', $data)) {
            $this->syncConsents($patient, $data['consents'], $data['consent_method'] ?? null, $user);
        }

        $this->audit->log(AuditAction::Update, $patient, $patient, 'Patient record updated', $before, [
            'changed' => array_keys($patient->getChanges()),
        ], $user);

        return $patient;
    }

    /**
     * @param  array<int, array<string, mixed>>  $kin
     */
    private function syncNextOfKin(Patient $patient, array $kin): void
    {
        $patient->nextOfKin()->delete();

        foreach ($kin as $index => $row) {
            if (empty($row['full_name']) || empty($row['relationship'])) {
                continue;
            }

            $patient->nextOfKin()->create([
                'full_name' => $row['full_name'],
                'relationship' => $row['relationship'],
                'phone' => $row['phone'] ?? null,
                'alt_phone' => $row['alt_phone'] ?? null,
                'address' => $row['address'] ?? null,
                'is_primary' => (bool) ($row['is_primary'] ?? $index === 0),
            ]);
        }
    }

    /**
     * Append-only consent sync: grants missing active consents and withdraws
     * previously granted ones. Rows are never deleted or rewritten.
     *
     * @param  array<string, mixed>  $consents
     */
    private function syncConsents(Patient $patient, array $consents, ?string $method, User $user): void
    {
        foreach ($consents as $purpose => $granted) {
            $purposeEnum = ConsentPurpose::tryFrom((string) $purpose);
            if (! $purposeEnum) {
                continue;
            }

            if ($granted) {
                if (! $patient->hasActiveConsent($purposeEnum)) {
                    $patient->consents()->create([
                        'facility_id' => $patient->facility_id,
                        'purpose' => $purposeEnum,
                        'granted' => true,
                        'method' => $method ?? 'verbal',
                        'granted_by' => $user->getKey(),
                        'granted_at' => now(),
                    ]);
                }

                continue;
            }

            $patient->consents()
                ->where('purpose', $purposeEnum->value)
                ->where('granted', true)
                ->whereNull('withdrawn_at')
                ->latest()
                ->first()
                ?->forceFill(['withdrawn_at' => now(), 'withdrawal_reason' => 'Consent not confirmed during record update'])
                ->save();
        }
    }

    public function addAllergy(Patient $patient, array $data, User $user): PatientAllergy
    {
        $allergy = $patient->allergies()->create([
            'facility_id' => $patient->facility_id,
            'substance' => $data['substance'],
            'substance_code' => $data['substance_code'] ?? null,
            'reaction' => $data['reaction'] ?? null,
            'severity' => $data['severity'] ?? 'moderate',
            'status' => 'active',
            'notes' => $data['notes'] ?? null,
            'recorded_by' => $user->getKey(),
            'recorded_at' => now(),
        ]);

        if ($patient->no_known_allergies_confirmed) {
            $patient->forceFill(['no_known_allergies_confirmed' => false])->save();
        }

        $this->audit->created($allergy, $patient);

        return $allergy;
    }

    public function confirmNoKnownAllergies(Patient $patient, User $user): void
    {
        $patient->forceFill(['no_known_allergies_confirmed' => true, 'updated_by' => $user->getKey()])->save();
        $this->audit->log(AuditAction::Update, $patient, $patient, 'No known allergies confirmed', [], [
            'no_known_allergies_confirmed' => true,
        ], $user);
    }

    /**
     * Merge the source record into the target and move all dependent data.
     */
    public function merge(Patient $source, Patient $target, string $reason, User $user): PatientMerge
    {
        if ($source->is($target)) {
            throw new \InvalidArgumentException('A patient cannot be merged into itself.');
        }

        return DB::transaction(function () use ($source, $target, $reason, $user) {
            $moved = [];

            foreach (self::PATIENT_TABLES as $table) {
                $ids = DB::table($table)->where('patient_id', $source->getKey())->pluck('id')->all();
                if ($ids !== []) {
                    DB::table($table)->whereIn('id', $ids)->update(['patient_id' => $target->getKey()]);
                    $moved[$table] = $ids;
                }
            }

            $merge = PatientMerge::create([
                'facility_id' => $source->facility_id,
                'source_patient_id' => $source->getKey(),
                'target_patient_id' => $target->getKey(),
                'reason' => $reason,
                'snapshot' => [
                    'patient' => $source->getAttributes(),
                    'moved' => $moved,
                ],
                'merged_by' => $user->getKey(),
                'merged_at' => now(),
            ]);

            $source->forceFill([
                'merge_status' => 'merged',
                'merged_into_patient_id' => $target->getKey(),
                'updated_by' => $user->getKey(),
            ])->save();

            $this->audit->log(AuditAction::Merge, $merge, $target, 'Patient merged into '.$target->mrn, [
                'source_mrn' => $source->mrn,
            ], ['target_mrn' => $target->mrn, 'reason' => $reason], $user);

            return $merge;
        });
    }

    public function unmerge(PatientMerge $merge, string $reason, User $user): void
    {
        if ($merge->isReversed()) {
            throw new \InvalidArgumentException('This merge has already been reversed.');
        }

        DB::transaction(function () use ($merge, $reason, $user) {
            $source = Patient::withTrashed()->findOrFail($merge->source_patient_id);
            $snapshot = $merge->snapshot;

            foreach (($snapshot['moved'] ?? []) as $table => $ids) {
                if (! in_array($table, self::PATIENT_TABLES, true)) {
                    continue;
                }
                DB::table($table)
                    ->whereIn('id', $ids)
                    ->where('patient_id', $merge->target_patient_id)
                    ->update(['patient_id' => $source->getKey()]);
            }

            $attributes = $snapshot['patient'] ?? [];
            unset($attributes['id'], $attributes['deleted_at'], $attributes['created_at'], $attributes['updated_at']);
            $source->forceFill($attributes + [
                'merge_status' => 'active',
                'merged_into_patient_id' => null,
                'updated_by' => $user->getKey(),
            ])->save();
            if ($source->trashed()) {
                $source->restore();
            }

            $merge->forceFill([
                'unmerged_by' => $user->getKey(),
                'unmerged_at' => now(),
                'unmerge_reason' => $reason,
            ])->save();

            $this->audit->log(AuditAction::Unmerge, $merge, $source, 'Patient merge reversed', [], [
                'reason' => $reason,
            ], $user);
        });
    }
}
