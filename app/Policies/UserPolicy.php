<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('user.view');
    }

    public function view(User $user, User $target): bool
    {
        return $this->viewAny($user) || $user->is($target);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('user.create');
    }

    public function update(User $user, User $target): bool
    {
        if ($user->is($target)) {
            return $user->hasPermission('user.update.self');
        }

        return $user->hasPermission('user.update');
    }

    public function assignRoles(User $user, ?User $target = null): bool
    {
        return $user->hasPermission('user.assign_role');
    }

    public function deactivate(User $user, User $target): bool
    {
        return $user->hasPermission('user.deactivate') && ! $user->is($target);
    }

    public function delete(User $user, ?User $target = null): bool
    {
        return $user->hasPermission('user.deactivate');
    }
}