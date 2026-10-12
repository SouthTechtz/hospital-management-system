<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTariff extends Model
{
    protected $fillable = [
        'facility_id', 'item_code', 'item_name', 'category', 'unit_price',
        'currency', 'is_exemptible', 'is_active', 'effective_from',
        'effective_to', 'verification_status',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'is_exemptible' => 'boolean',
            'is_active' => 'boolean',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }
}
