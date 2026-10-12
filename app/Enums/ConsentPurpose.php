<?php

namespace App\Enums;

enum ConsentPurpose: string
{
    case Treatment = 'treatment';
    case DataSharing = 'data_sharing';
    case Research = 'research';
    case Audit = 'audit';

    public function labelKey(): string
    {
        return 'enums.consent_purpose.'.$this->value;
    }
}
