<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'item_type', 'drug_id', 'description', 'dose', 'dose_unit',
        'route', 'frequency', 'duration_days', 'quantity', 'quantity_unit',
        'is_prn', 'instructions', 'version', 'voided_at', 'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_prn' => 'boolean',
            'voided_at' => 'datetime',
            'quantity' => 'decimal:2',
            'version' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function drug(): BelongsTo
    {
        return $this->belongsTo(Drug::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('voided_at');
    }

    public function signature(): string
    {
        $parts = array_filter([
            $this->drug?->displayName() ?? $this->description,
            $this->dose,
            $this->dose_unit,
            $this->route,
            $this->frequency,
        ]);

        return implode(' ', $parts);
    }
}
