<?php

namespace App\Enums;

enum ClinicalAction: string
{
    case Prescribe = 'prescribe';
    case Administer = 'administer';
    case Dispense = 'dispense';
    case Admin = 'admin';

    public function labelKey(): string
    {
        return 'enums.clinical_action.'.$this->value;
    }
}
