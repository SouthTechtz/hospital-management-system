<?php

namespace App\Enums;

enum BreakGlassStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Reviewed = 'reviewed';

    public function labelKey(): string
    {
        return 'enums.break_glass_status.'.$this->value;
    }
}
