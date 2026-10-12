<?php

namespace App\Enums;

enum EncounterStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function labelKey(): string
    {
        return 'enums.encounter_status.'.$this->value;
    }
}
