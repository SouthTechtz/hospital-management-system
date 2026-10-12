<?php

namespace App\Models;

use App\Enums\ConsentPurpose;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consent extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id', 'patient_id', 'purpose', 'granted', 'method',
        'evidence_reference', 'notes', 'granted_by', 'granted_at',
        'withdrawn_at', 'withdrawal_reason',
    ];

    protected function casts(): array
    {
        return [
            'purpose' => ConsentPurpose::class,
            'granted' => 'boolean',
            'granted_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function hasActiveConsent(ConsentPurpose $purpose): bool
    {
        return $this->purpose === $purpose && $this->granted && $this->withdrawn_at === null;
    }
}
