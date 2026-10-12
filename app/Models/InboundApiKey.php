<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Inbound API key issued to another system. Only the SHA-256 hash is stored;
 * the plaintext is shown once at creation (Part B).
 */
class InboundApiKey extends Model
{
    protected $fillable = [
        'facility_id', 'name', 'key_prefix', 'key_hash', 'scopes',
        'rate_limit_per_minute', 'expires_at', 'last_used_at', 'request_count',
        'is_active', 'created_by', 'revoked_at',
    ];

    protected $hidden = ['key_hash'];

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(InboundApiKeyUsage::class);
    }

    public function isUsable(): bool
    {
        return $this->is_active
            && $this->revoked_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
