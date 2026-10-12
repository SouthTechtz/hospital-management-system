<?php

namespace App\Enums;

enum QueueEntryStatus: string
{
    case Waiting = 'waiting';
    case Called = 'called';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Removed = 'removed';

    public function labelKey(): string
    {
        return 'enums.queue_entry_status.'.$this->value;
    }
}
