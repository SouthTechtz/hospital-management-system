<?php

namespace App\Services;

use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;

/**
 * Concurrency-safe counters backed by a locked row (SELECT ... FOR UPDATE).
 * Portable across MySQL 8 and MariaDB, guaranteed collision-free under
 * concurrent use (Phase 1 acceptance criterion: MRN generation).
 */
class SequenceService
{
    public function next(string $name, ?int $facilityId = null): int
    {
        return DB::transaction(function () use ($name, $facilityId) {
            $row = DocumentSequence::query()
                ->where('name', $name)
                ->when($facilityId === null, fn ($q) => $q->whereNull('facility_id'), fn ($q) => $q->where('facility_id', $facilityId))
                ->lockForUpdate()
                ->first();

            if ($row === null) {
                DocumentSequence::insertOrIgnore([
                    'facility_id' => $facilityId,
                    'name' => $name,
                    'next_value' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DocumentSequence::query()
                    ->where('name', $name)
                    ->when($facilityId === null, fn ($q) => $q->whereNull('facility_id'), fn ($q) => $q->where('facility_id', $facilityId))
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $value = (int) $row->next_value;
            $row->forceFill(['next_value' => $value + 1])->save();

            return $value;
        }, 5);
    }
}
