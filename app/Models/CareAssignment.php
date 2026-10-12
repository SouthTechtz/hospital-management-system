<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Links a clinician or a department to a patient for a period of care. This is
 * the care-relationship basis of the patient policy (Part B).
 */
class CareAssignment extends Model
{
    protected $fillable = [
        'facility_id', 'patient_id', 'user_id', 'department_id', 'relationship',
        'assigned_by', 'assigned_at', 'released_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('released_at');
    }
}
