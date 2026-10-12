<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
{
    use HandlesAuthorization;

    public function manage(User $user, ?Department $department = null): bool
    {
        return $user->hasPermission('department.manage') || $user->isIctAdministrator();
    }
}