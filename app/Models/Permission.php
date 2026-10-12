<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    /** @use HasFactory<\Database\Factories\PermissionFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'module', 'description_en', 'description_sw',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('granted')->withTimestamps();
    }

    public function displayDescription(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return ($locale === 'sw' ? $this->description_sw : $this->description_en) ?: $this->name;
    }
}
