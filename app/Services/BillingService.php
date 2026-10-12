<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\ExemptionCategory;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Models\Encounter;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(
        private readonly SequenceService $sequences,
        private readonly AuditLogger $audit,
    ) {
    }

    public function openInvoice(Patient $patient, ?Encounter $encounter, User $user): Invoice
    {
        return DB::transaction(function () use ($patient, $encounter, $user) {
            $number = $this->nextInvoiceNumber($patient->facility_id);

            $invoice = Invoice::create([
                'facility_id' => $patient->facility_id,
                'patient_id' => $patient->getKey(),
                'encounter_id' => $encounter?->getKey(),
                'invoice_number' => $number,
                'payer_type' => $patient->payer_type?->value ?? 'cash',
                'status' => InvoiceStatus::Open,
                'subtotal' => 0,
                'exemption_amount' => 0,
                'total' => 0,
                'amount_paid' => 0,
                'balance' => 0,
                'created_by' => $user->getKey(),
            ]);

            $this->audit->log(AuditAction::Create, $invoice, $patient, 'Invoice opened', [], [
                'invoice_number' => $number,
            ], $user);

            return $invoice;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addItem(Invoice $invoice, array $data, User $user, ?ExemptionCategory $exemption = null, ?string $exemptionReason = null): InvoiceItem
    {
        return DB::transaction(function () use ($invoice, $data, $user, $exemption, $exemptionReason) {
            $quantity = (float) ($data['quantity'] ?? 1);
            $unitPrice = (float) ($data['unit_price'] ?? 0);
            $lineTotal = round($quantity * $unitPrice, 2);

            $item = InvoiceItem::create([
                'invoice_id' => $invoice->getKey(),
                'item_code' => $data['item_code'] ?? null,
                'description' => $data['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
                'is_exempt' => $exemption !== null,
                'exemption_category' => $exemption?->value,
                'exemption_reason' => $exemptionReason,
                'source_type' => $data['source_type'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'added_by' => $user->getKey(),
            ]);

            $invoice->recalculate();

            $this->audit->log(AuditAction::Create, $item, $invoice->patient, 'Invoice line added', [], [
                'description' => $data['description'],
                'line_total' => $lineTotal,
                'is_exempt' => $exemption !== null,
            ], $user);

            return $item;
        });
    }

    public function recordPayment(Invoice $invoice, float $amount, PaymentMethod $method, ?string $reference, User $user): Payment
    {
        return DB::transaction(function () use ($invoice, $amount, $method, $reference, $user) {
            if ($amount <= 0) {
                throw new \InvalidArgumentException('Payment amount must be greater than zero.');
            }

            $payment = Payment::create([
                'facility_id' => $invoice->facility_id,
                'invoice_id' => $invoice->getKey(),
                'amount' => round($amount, 2),
                'method' => $method,
                'reference' => $reference,
                'received_by' => $user->getKey(),
                'paid_at' => now(),
            ]);

            $invoice->recalculate();

            $this->audit->log(AuditAction::Create, $payment, $invoice->patient, 'Payment recorded', [], [
                'amount' => $payment->amount,
                'method' => $method->value,
            ], $user);

            return $payment;
        });
    }

    public function voidPayment(Payment $payment, string $reason, User $user): void
    {
        $payment->forceFill(['voided_at' => now(), 'void_reason' => $reason])->save();
        $payment->invoice->recalculate();

        $this->audit->log(AuditAction::Void, $payment, $payment->invoice->patient, 'Payment voided', [], [
            'reason' => $reason,
        ], $user);
    }

    public function voidInvoice(Invoice $invoice, string $reason, User $user): void
    {
        $invoice->items()->update([
            'is_exempt' => false,
        ]);

        $invoice->forceFill([
            'status' => InvoiceStatus::Cancelled,
            'voided_at' => now(),
            'void_reason' => $reason,
        ])->save();

        $this->audit->log(AuditAction::Void, $invoice, $invoice->patient, 'Invoice voided', [], [
            'reason' => $reason,
        ], $user);
    }

    public function nextInvoiceNumber(int $facilityId): string
    {
        $value = $this->sequences->next('invoice', $facilityId);

        return 'INV-'.str_pad((string) $value, 8, '0', STR_PAD_LEFT);
    }
}