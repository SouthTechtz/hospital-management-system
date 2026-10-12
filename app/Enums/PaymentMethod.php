<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case MobileMoney = 'mobile_money';
    case BankTransfer = 'bank_transfer';
    case Nhif = 'nhif';
    case Waiver = 'waiver';

    public function labelKey(): string
    {
        return 'enums.payment_method.'.$this->value;
    }
}
