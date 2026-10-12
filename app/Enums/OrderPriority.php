<?php

namespace App\Enums;

enum OrderPriority: string
{
    case Routine = 'routine';
    case Urgent = 'urgent';
    case Stat = 'stat';

    public function labelKey(): string
    {
        return 'enums.order_priority.'.$this->value;
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::Stat => 1,
            self::Urgent => 2,
            self::Routine => 3,
        };
    }
}
