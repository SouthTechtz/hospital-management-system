<?php

namespace App\Policies;

use App\Models\BreakGlassAccess;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BreakGlassAccessPolicy
{
    use HandlesAuthorization;

    public function use(User $user): bool
    {
        return $user->hasPermission('breakglass.use');
    }

    public function review(User $user, ?BreakGlassAccess $access = null): bool
    {
        return $user->hasPermission('breakglass.review');
    }
}