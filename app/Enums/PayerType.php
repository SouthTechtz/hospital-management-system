<?php

namespace App\Enums;

/**
 * Payers recognised by the facility. NHIF is the national scheme; cash and
 * exemption cover the remainder. Values are configuration-driven in the
 * billing service and are never invented (see docs/ASSUMPTIONS.md).
 */
enum PayerType: string
{
    case Cash = 'cash';
    case Nhif = 'nhif';
    case Exemption = 'exemption';
    case Other = 'other';

    public function labelKey(): string
    {
        return 'enums.payer_type.'.$this->value;
    }

    public function requiresVerification(): bool
    {
        return $this === self::Nhif;
    }
}
