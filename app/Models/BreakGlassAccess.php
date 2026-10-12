<?php

namespace App\Models;

use App\Enums\BreakGlassStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakGlassAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'facility_id', 'user_id', 'patient_id', 'reason_code', 'reason_text',
        'scope', 'granted_at', 'expires_at', 'status', 'privacy_officer_notified',
        'privacy_officer_notified_at', 'reviewed_by', 'reviewed_at',
        'review_outcome', 'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'scope' => 'array',
            'granted_at' => 'datetime',
            'expires_at' => 'datetime',
            'status' => BreakGlassStatus::class,
            'privacy_officer_notified' => 'boolean',
            'privacy_officer_notified_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isActiveNow(): bool
    {
        return $this->status === BreakGlassStatus::Active && $this->expires_at->isFuture();
    }
}
