<?php

namespace App\Models;

use App\Enums\DiagnosisType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnosis extends Model
{
    protected $fillable = [
        'facility_id', 'patient_id', 'encounter_id', 'consultation_id',
        'icd10_code_id', 'icd10_code', 'icd10_description', 'icd11_code',
        'type', 'notes', 'version', 'previous_version_id', 'voided_at',
        'void_reason', 'diagnosed_by', 'diagnosed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => DiagnosisType::class,
            'voided_at' => 'datetime',
            'diagnosed_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function icd10(): BelongsTo
    {
        return $this->belongsTo(Icd10Code::class, 'icd10_code_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('voided_at');
    }
}
