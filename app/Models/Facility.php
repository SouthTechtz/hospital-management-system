<?php

namespace App\Models;

use Database\Factories\FacilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    /** @use HasFactory<FacilityFactory> */
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'short_name', 'type', 'hfr_code', 'region', 'district',
        'ward', 'physical_address', 'postal_address', 'phone', 'email', 'timezone',
        'currency', 'logo_path', 'settings', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'phone' => 'encrypted',
            'email' => 'encrypted',
        ];
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
