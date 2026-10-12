<?php

namespace App\Models;

use App\Enums\TriageCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TriageAssessment extends Model
{
    protected $fillable = [
        'facility_id', 'encounter_id', 'patient_id', 'category', 'chief_complaint',
        'notes', 'version', 'previous_version_id', 'voided_at', 'void_reason',
        'triaged_by', 'triaged_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => TriageCategory::class,
            'voided_at' => 'datetime',
            'triaged_at' => 'datetime',
        ];
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function triagedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triaged_by');
    }
}
