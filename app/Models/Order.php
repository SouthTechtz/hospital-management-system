<?php

namespace App\Models;

use App\Enums\OrderPriority;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'facility_id', 'patient_id', 'encounter_id', 'consultation_id',
        'order_type', 'status', 'priority', 'notes', 'version',
        'previous_version_id', 'voided_at', 'void_reason', 'ordered_by',
        'ordered_at',
    ];

    protected function casts(): array
    {
        return [
            'order_type' => OrderType::class,
            'status' => OrderStatus::class,
            'priority' => OrderPriority::class,
            'voided_at' => 'datetime',
            'ordered_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('voided_at');
    }
}
