<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundApiKeyUsage extends Model
{
    protected $table = 'inbound_api_key_usage';

    public const UPDATED_AT = null;

    protected $fillable = [
        'inbound_api_key_id', 'method', 'path', 'status_code', 'ip_address', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public function key(): BelongsTo
    {
        return $this->belongsTo(InboundApiKey::class, 'inbound_api_key_id');
    }
}
