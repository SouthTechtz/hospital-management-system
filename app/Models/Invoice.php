<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\PayerType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'facility_id', 'patient_id', 'encounter_id', 'invoice_number',
        'payer_type', 'status', 'subtotal', 'exemption_amount', 'total',
        'amount_paid', 'balance', 'notes', 'voided_at', 'void_reason', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payer_type' => PayerType::class,
            'status' => InvoiceStatus::class,
            'subtotal' => 'decimal:2',
            'exemption_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance' => 'decimal:2',
            'voided_at' => 'datetime',
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

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    /**
     * Recalculate denormalised totals from line items and payments.
     */
    public function recalculate(): void
    {
        $subtotal = $this->items->sum(fn (InvoiceItem $item) => (float) $item->line_total);
        $exemption = $this->items->where('is_exempt', true)->sum(fn (InvoiceItem $item) => (float) $item->line_total);
        $paid = $this->payments->whereNull('voided_at')->sum(fn (Payment $payment) => (float) $payment->amount);

        $total = $subtotal - $exemption;

        $this->subtotal = $subtotal;
        $this->exemption_amount = $exemption;
        $this->total = max($total, 0);
        $this->amount_paid = $paid;
        $this->balance = max($this->total - $paid, 0);

        $this->status = match (true) {
            $this->status === InvoiceStatus::Cancelled => InvoiceStatus::Cancelled,
            $this->total <= 0 => InvoiceStatus::Waived,
            $paid <= 0 => InvoiceStatus::Open,
            $paid + 0.001 < $this->total => InvoiceStatus::PartiallyPaid,
            default => InvoiceStatus::Paid,
        };

        $this->save();
    }
}
