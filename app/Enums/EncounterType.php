<?php

namespace App\Enums;

enum EncounterType: string
{
    case Opd = 'opd';
    case Ipd = 'ipd';
    case Emergency = 'emergency';
    case Maternity = 'maternity';
    case Paediatric = 'paediatric';
    case Theatre = 'theatre';
    case Laboratory = 'laboratory';
    case Radiology = 'radiology';
    case Pharmacy = 'pharmacy';

    public function labelKey(): string
    {
        return 'enums.encounter_type.'.$this->value;
    }

    /**
     * Outpatient-style encounters that open a consultation directly.
     */
    public function isOutpatient(): bool
    {
        return in_array($this, [self::Opd, self::Emergency, self::Paediatric], true);
    }
}
