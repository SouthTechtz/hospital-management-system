<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'facility_id', 'encounter_id', 'patient_id', 'history', 'examination',
        'plan', 'disposition', 'follow_up_date', 'version', 'previous_version_id',
        'voided_at', 'void_reason', 'created_by', 'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_date' => 'date',
            'voided_at' => 'datetime',
            'signed_at' => 'datetime',
            'version' => 'integer',
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

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isActive(): bool
    {
        return $this->voided_at === null;
    }
}
