<?php

namespace App\Enums;

enum DiagnosisType: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Provisional = 'provisional';
    case Final = 'final';

    public function labelKey(): string
    {
        return 'enums.diagnosis_type.'.$this->value;
    }
}
