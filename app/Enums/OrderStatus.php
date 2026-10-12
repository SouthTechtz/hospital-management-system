<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Draft = 'draft';
    case Ordered = 'ordered';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function labelKey(): string
    {
        return 'enums.order_status.'.$this->value;
    }
}
