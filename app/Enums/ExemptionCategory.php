<?php

namespace App\Enums;

enum ExemptionCategory: string
{
    case ChildUnderFive = 'child_under_five';
    case Elderly = 'elderly';
    case Pregnant = 'pregnant';
    case Indigent = 'indigent';
    case ChronicCondition = 'chronic_condition';
    case Staff = 'staff';
    case Other = 'other';

    public function labelKey(): string
    {
        return 'enums.exemption_category.'.$this->value;
    }
}
