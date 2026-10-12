<?php

namespace App\Enums;

enum OrderType: string
{
    case Medication = 'medication';
    case Laboratory = 'laboratory';
    case Radiology = 'radiology';
    case Procedure = 'procedure';
    case Referral = 'referral';

    public function labelKey(): string
    {
        return 'enums.order_type.'.$this->value;
    }
}
