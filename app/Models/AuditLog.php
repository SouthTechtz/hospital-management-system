<?php

namespace App\Models;

use App\Enums\AuditAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Append-only audit entry. There is no `updated_at` and the table is protected
 * by database triggers (Part B).
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'facility_id', 'user_id', 'actor_name', 'actor_role', 'action',
        'auditable_type', 'auditable_id', 'patient_id', 'description',
        'old_values', 'new_values', 'ip_address', 'user_agent', 'device',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'action' => AuditAction::class,
            'old_values' => 'array',
            'new_values' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
