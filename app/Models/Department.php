<?php

namespace App\Models;

use App\Enums\Directorate;
use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory;

    protected $fillable = [
        'facility_id', 'code', 'name', 'directorate', 'location',
        'is_clinical', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'directorate' => Directorate::class,
            'is_clinical' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
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
