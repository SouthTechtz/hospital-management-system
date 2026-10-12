<?php

namespace App\Http\Controllers;

use App\Enums\ExemptionCategory;
use App\Enums\PaymentMethod;
use App\Models\Encounter;
use App\Models\Exemption;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\ServiceTariff;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Cash and exemption billing (Part E Phase 1; Part D7.12 is expanded in Phase 4
 * with GePG/NHIF). Denormalised totals are recalculated from line items and
 * payments on every change.
 */
class BillingController extends Controller
{
    public function __construct(private readonly BillingService $billing)
    {
    }

    public function patientBilling(Patient $patient, Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $invoices = Invoice::with('items', 'payments', 'encounter')
            ->where('patient_id', $patient->getKey())
            ->latest()
            ->get();

        return view('billing.patient', [
            'patient' => $patient,
            'invoices' => $invoices,
            'tariffs' => ServiceTariff::where('is_active', true)->orderBy('category')->orderBy('item_name')->get(),
            'activeExemption' => $patient->activeExemptions()->first(),
        ]);
    }

    public function openInvoice(Patient $patient, Request $request)
    {
        $this->authorize('create', Invoice::class);

        $encounter = Encounter::where('patient_id', $patient->getKey())
            ->where('status', 'open')
            ->latest()
            ->first();

        $invoice = $this->billing->openInvoice($patient, $encounter, $request->user());

        return redirect()->back()->with('status', __('billing.invoice_opened', ['number' => $invoice->invoice_number]));
    }

    public function addItem(Invoice $invoice, Request $request)
    {
        $this->authorize('update', $invoice);

        $data = $request->validate([
            'description' => ['required', 'string', 'max:300'],
            'quantity' => ['required', 'numeric', 'between:0.01,100000'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'item_code' => ['nullable', 'string', 'max:40'],
            'is_exempt' => ['nullable', 'boolean'],
            'exemption_category' => ['nullable', Rule::enum(ExemptionCategory::class)],
            'exemption_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $exemption = $data['is_exempt'] ?? false
            ? ExemptionCategory::tryFrom((string) ($data['exemption_category'] ?? 'other')) ?? ExemptionCategory::Other
            : null;

        $this->billing->addItem(
            $invoice,
            $data,
            $request->user(),
            $exemption,
            $data['exemption_reason'] ?? null,
        );

        return redirect()->back()->with('status', __('billing.item_added'));
    }

    public function recordPayment(Invoice $invoice, Request $request)
    {
        $this->authorize('pay', $invoice);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:120'],
        ]);

        $this->billing->recordPayment(
            $invoice,
            (float) $data['amount'],
            PaymentMethod::from($data['method']),
            $data['reference'] ?? null,
            $request->user(),
        );

        return redirect()->back()->with('status', __('billing.payment_recorded'));
    }

    public function voidPayment(Payment $payment, Request $request)
    {
        $this->authorize('void', $payment->invoice);

        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:500']]);

        $this->billing->voidPayment($payment, $data['reason'], $request->user());

        return redirect()->back()->with('status', __('billing.payment_voided'));
    }

    public function voidInvoice(Invoice $invoice, Request $request)
    {
        $this->authorize('void', $invoice);

        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:500']]);

        $this->billing->voidInvoice($invoice, $data['reason'], $request->user());

        return redirect()->back()->with('status', __('billing.invoice_voided'));
    }
}