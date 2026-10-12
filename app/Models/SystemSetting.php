<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    protected $fillable = [
        'key', 'value', 'group', 'type', 'is_encrypted',
        'verification_status', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'encrypted',
            'is_encrypted' => 'boolean',
        ];
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
