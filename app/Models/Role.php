<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;

    protected $fillable = [
        'name', 'display_name_en', 'display_name_sw', 'scope',
        'description_en', 'description_sw', 'requires_mfa', 'is_system',
    ];

    protected function casts(): array
    {
        return [
            'requires_mfa' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function displayName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $locale === 'sw' ? $this->display_name_sw : $this->display_name_en;
    }

    /**
     * Sync permissions and immediately invalidate the affected users' caches.
     */
    public function syncPermissionsSafely(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);

        $this->users()->select('users.id')->each(function (User $user) {
            $user->forgetPermissionCache();
        });
    }
}
