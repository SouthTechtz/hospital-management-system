<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    protected $fillable = [
        'code', 'generic_name', 'brand_name', 'strength', 'form', 'route',
        'unit', 'atc_code', 'is_controlled', 'is_essential',
        'requires_prescription', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_controlled' => 'boolean',
            'is_essential' => 'boolean',
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function displayName(): string
    {
        return trim($this->generic_name.' '.$this->strength);
    }
}
