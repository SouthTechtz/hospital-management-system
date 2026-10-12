<?php

namespace App\Models;

use App\Enums\SystemRole;
use App\Support\SensitiveHasher;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username', 'first_name', 'middle_name', 'last_name', 'job_title',
        'registration_no', 'email', 'phone', 'password', 'facility_id',
        'department_id', 'primary_role_id', 'must_change_password', 'is_active',
        'preferred_locale', 'mfa_enabled', 'mfa_secret', 'mfa_confirmed_at',
        'failed_login_attempts', 'locked_until', 'last_login_at', 'last_login_ip',
        'password_changed_at', 'created_by', 'deactivated_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'mfa_secret',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email' => 'encrypted',
            'phone' => 'encrypted',
            'mfa_secret' => 'encrypted',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
            'mfa_enabled' => 'boolean',
            'mfa_confirmed_at' => 'datetime',
            'locked_until' => 'datetime',
            'password_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'deactivated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->isDirty('email')) {
                $user->email_hash = SensitiveHasher::email($user->email);
            }
            if ($user->isDirty('phone')) {
                $user->phone_hash = SensitiveHasher::phone($user->phone);
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name, $this->middle_name, $this->last_name,
        ])));
    }

    public function getInitialsAttribute(): string
    {
        $first = mb_substr((string) $this->first_name, 0, 1);
        $last = mb_substr((string) $this->last_name, 0, 1);

        return mb_strtoupper($first.$last);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function primaryRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'primary_role_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withPivot('granted')->withTimestamps();
    }

    public function careAssignments(): HasMany
    {
        return $this->hasMany(CareAssignment::class);
    }

    /**
     * Effective permission names (direct denies win, then direct grants, then
     * role grants). Cached and invalidated on any permission change.
     *
     * @return Collection<int, string>
     */
    public function permissionNames(): Collection
    {
        return Cache::remember(
            $this->permissionCacheKey(),
            now()->addMinutes(30),
            function () {
                $denied = $this->directPermissions
                    ->where('pivot.granted', false)
                    ->pluck('name');

                $granted = $this->directPermissions
                    ->where('pivot.granted', true)
                    ->pluck('name');

                $rolePermissions = $this->roles
                    ->flatMap(fn (Role $role) => $role->permissions->pluck('name'));

                return $granted
                    ->merge($rolePermissions)
                    ->unique()
                    ->reject(fn ($name) => $denied->contains($name))
                    ->values();
            }
        );
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isIctAdministrator()) {
            return true;
        }

        return $this->permissionNames()->contains($permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return $this->permissionNames()->intersect($permissions)->isNotEmpty();
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }

    public function hasRoleEnum(SystemRole $role): bool
    {
        return $this->hasRole($role->value);
    }

    public function isIctAdministrator(): bool
    {
        return $this->hasRoleEnum(SystemRole::IctAdministrator);
    }

    public function isPrivacyOfficer(): bool
    {
        return $this->hasRoleEnum(SystemRole::PrivacyOfficer);
    }

    /**
     * MFA is mandatory for administrators and prescribers (Part B).
     */
    public function requiresMfa(): bool
    {
        if ($this->roles->contains(fn (Role $role) => $role->requires_mfa)) {
            return true;
        }

        return $this->roles->contains(fn (Role $role) => SystemRole::tryFrom($role->name)?->requiresMfa() ?? false);
    }

    public function forgetPermissionCache(): void
    {
        Cache::forget($this->permissionCacheKey());
    }

    private function permissionCacheKey(): string
    {
        return 'user.permissions.'.$this->getKey();
    }

    public function routeNotificationForMail(): ?string
    {
        return $this->email;
    }
}
