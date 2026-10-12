<?php

namespace App\Enums;

/**
 * Triage categories. Colour is never the only signal: each category always
 * renders with its text label (Part D3/D8).
 */
enum TriageCategory: string
{
    case Red = 'red';
    case Orange = 'orange';
    case Yellow = 'yellow';
    case Green = 'green';
    case Blue = 'blue';

    public function labelKey(): string
    {
        return 'enums.triage_category.'.$this->value;
    }

    public function severity(): string
    {
        return match ($this) {
            self::Red, self::Orange => 'critical',
            self::Yellow => 'warning',
            self::Green => 'success',
            self::Blue => 'info',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::Red => 1,
            self::Orange => 2,
            self::Yellow => 3,
            self::Green => 4,
            self::Blue => 5,
        };
    }
}
