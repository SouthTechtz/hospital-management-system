<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('role.view');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('role.manage');
    }

    public function update(User $user, Role $role): bool
    {
        return $this->manage($user) && ! $role->is_system;
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->update($user, $role) && $role->users()->doesntExist();
    }
}