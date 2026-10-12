<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuditLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('audit.view');
    }

    public function view(User $user, AuditLog $log): bool
    {
        return $this->viewAny($user);
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('audit.export');
    }
}