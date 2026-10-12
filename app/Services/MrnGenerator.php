<?php

namespace App\Services;

class MrnGenerator
{
    public function __construct(private readonly SequenceService $sequences)
    {
    }

    /**
     * Format: {prefix}-{zero-padded sequential number} e.g. MRN-0000001.
     * Uniqueness is additionally enforced by a database unique index on
     * (facility_id, mrn).
     */
    public function generate(int $facilityId): string
    {
        $value = $this->sequences->next('mrn', $facilityId);
        $prefix = (string) config('hms.mrn.prefix', 'MRN');
        $width = (int) config('hms.mrn.sequence_width', 7);

        return $prefix.'-'.str_pad((string) $value, $width, '0', STR_PAD_LEFT);
    }
}
