<?php

namespace App\Models;

use App\Enums\ExemptionCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exemption extends Model
{
    protected $fillable = [
        'facility_id', 'patient_id', 'category', 'document_reference', 'reason',
        'valid_from', 'valid_until', 'is_active', 'approved_by', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => ExemptionCategory::class,
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function isCurrent(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now()->startOfDay();

        return ($this->valid_from === null || $this->valid_from->lte($now))
            && ($this->valid_until === null || $this->valid_until->gte($now));
    }
}
