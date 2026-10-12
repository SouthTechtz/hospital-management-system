<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Outbound secrets used to call national systems. Values are encrypted at rest
 * and are never displayed after saving or written to logs (Part B).
 */
class OutboundCredential extends Model
{
    protected $fillable = [
        'provider', 'label', 'username', 'secret', 'extra', 'environment',
        'base_url', 'verification_status', 'is_active', 'updated_by',
    ];

    protected $hidden = ['username', 'secret', 'extra'];

    protected function casts(): array
    {
        return [
            'username' => 'encrypted',
            'secret' => 'encrypted',
            'extra' => 'encrypted:array',
            'is_active' => 'boolean',
        ];
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
