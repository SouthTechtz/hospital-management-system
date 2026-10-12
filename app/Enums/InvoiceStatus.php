<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Open = 'open';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Waived = 'waived';
    case Cancelled = 'cancelled';

    public function labelKey(): string
    {
        return 'enums.invoice_status.'.$this->value;
    }
}
