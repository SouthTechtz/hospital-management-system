<?php

namespace App\Enums;

enum Sex: string
{
    case Male = 'male';
    case Female = 'female';
    case Unknown = 'unknown';

    public function labelKey(): string
    {
        return 'enums.sex.'.$this->value;
    }
}
