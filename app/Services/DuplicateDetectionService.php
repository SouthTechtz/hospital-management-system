<?php

namespace App\Services;

use App\Models\Patient;
use App\Support\SensitiveHasher;
use Illuminate\Support\Collection;

/**
 * Deterministic duplicate detection for the Master Patient Index (Part C).
 * Scores are rule-based and every reason is shown to the user.
 */
class DuplicateDetectionService
{
    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, array{patient:Patient, score:int, reasons:array<int,string>}>
     */
    public function find(array $data, ?int $facilityId = null, ?int $excludeId = null): Collection
    {
        $firstName = trim((string) ($data['first_name'] ?? ''));
        $middleName = trim((string) ($data['middle_name'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? ''));
        $normalized = Patient::normalizeName($firstName.' '.$middleName.' '.$lastName);
        $dob = $data['date_of_birth'] ?? null;

        $hashes = [
            'national_id' => SensitiveHasher::hash($data['national_id'] ?? null),
            'nida' => SensitiveHasher::hash($data['nida_number'] ?? null),
            'nhif' => SensitiveHasher::hash($data['nhif_number'] ?? null),
            'phone' => SensitiveHasher::phone($data['phone'] ?? null),
        ];

        $query = Patient::query()->where('merge_status', 'active');

        if ($facilityId) {
            $query->where('facility_id', $facilityId);
        }

        if ($excludeId) {
            $query->whereKeyNot($excludeId);
        }

        $query->where(function ($q) use ($hashes, $normalized, $dob) {
            foreach ([
                'national_id_hash' => $hashes['national_id'],
                'nida_hash' => $hashes['nida'],
                'nhif_hash' => $hashes['nhif'],
                'phone_hash' => $hashes['phone'],
            ] as $column => $hash) {
                if ($hash) {
                    $q->orWhere($column, $hash);
                }
            }

            if ($normalized !== '') {
                $q->orWhere('full_name_normalized', $normalized);
                if ($dob) {
                    $q->orWhere(fn ($inner) => $inner->where('date_of_birth', $dob));
                }
            }
        });

        return $query->limit(50)->get()->map(function (Patient $candidate) use ($hashes, $normalized, $dob) {
            $score = 0;
            $reasons = [];

            foreach ([
                'national_id_hash' => ['national_id', 100, 'national_id'],
                'nida_hash' => ['nida', 100, 'nida'],
                'nhif_hash' => ['nhif', 100, 'nhif'],
                'phone_hash' => ['phone', 80, 'phone'],
            ] as $column => [$key, $weight, $reason]) {
                if ($hashes[$key] && $candidate->{$column} === $hashes[$key]) {
                    $score += $weight;
                    $reasons[] = $reason;
                }
            }

            if ($candidate->full_name_normalized === $normalized && $normalized !== '') {
                $score += 60;
                $reasons[] = 'name';

                if ($dob && $candidate->date_of_birth?->toDateString() === $dob) {
                    $score += 25;
                    $reasons[] = 'date_of_birth';
                }
            }

            return [
                'patient' => $candidate,
                'score' => min($score, 100),
                'reasons' => array_values(array_unique($reasons)),
            ];
        })->sortByDesc('score')->values();
    }
}
