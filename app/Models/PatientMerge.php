<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMerge extends Model
{
    protected $fillable = [
        'facility_id', 'source_patient_id', 'target_patient_id', 'reason',
        'snapshot', 'merged_by', 'merged_at', 'unmerged_by', 'unmerged_at',
        'unmerge_reason',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'merged_at' => 'datetime',
            'unmerged_at' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'source_patient_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'target_patient_id');
    }

    public function isReversed(): bool
    {
        return $this->unmerged_at !== null;
    }
}
