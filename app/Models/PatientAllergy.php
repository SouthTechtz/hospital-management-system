<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientAllergy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'facility_id', 'patient_id', 'substance', 'substance_code', 'reaction',
        'severity', 'status', 'notes', 'recorded_by', 'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
