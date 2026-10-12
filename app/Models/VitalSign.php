<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSign extends Model
{
    protected $fillable = [
        'facility_id', 'patient_id', 'encounter_id', 'temperature_c', 'pulse',
        'respiratory_rate', 'systolic', 'diastolic', 'spo2', 'weight_kg',
        'height_cm', 'muac_mm', 'bmi', 'pain_score', 'gcs', 'notes',
        'recorded_by', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'temperature_c' => 'decimal:1',
            'weight_kg' => 'decimal:2',
            'height_cm' => 'decimal:1',
            'bmi' => 'decimal:1',
            'recorded_at' => 'datetime',
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

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Deterministic range flags for display (no "intelligence", Part D8).
     * Each flag returns null when within range, else a severity + label key.
     *
     * @return array<string, array{severity:string,label:string}>
     */
    public function rangeFlags(): array
    {
        $flags = [];

        if ($this->temperature_c !== null) {
            if ($this->temperature_c >= 38.0) {
                $flags['temperature'] = ['severity' => 'warning', 'label' => 'high'];
            } elseif ($this->temperature_c < 35.0) {
                $flags['temperature'] = ['severity' => 'warning', 'label' => 'low'];
            }
        }

        if ($this->pulse !== null) {
            if ($this->pulse > 100) {
                $flags['pulse'] = ['severity' => 'warning', 'label' => 'high'];
            } elseif ($this->pulse < 60) {
                $flags['pulse'] = ['severity' => 'warning', 'label' => 'low'];
            }
        }

        if ($this->systolic !== null && $this->diastolic !== null) {
            if ($this->systolic >= 140 || $this->diastolic >= 90) {
                $flags['blood_pressure'] = ['severity' => 'warning', 'label' => 'high'];
            } elseif ($this->systolic < 90 || $this->diastolic < 60) {
                $flags['blood_pressure'] = ['severity' => 'critical', 'label' => 'low'];
            }
        }

        if ($this->spo2 !== null && $this->spo2 < 95) {
            $flags['spo2'] = [
                'severity' => $this->spo2 < 90 ? 'critical' : 'warning',
                'label' => 'low',
            ];
        }

        if ($this->respiratory_rate !== null) {
            if ($this->respiratory_rate > 24) {
                $flags['respiratory_rate'] = ['severity' => 'warning', 'label' => 'high'];
            } elseif ($this->respiratory_rate < 12) {
                $flags['respiratory_rate'] = ['severity' => 'warning', 'label' => 'low'];
            }
        }

        return $flags;
    }
}
