<?php

namespace App\Policies;

use App\Models\OutboundCredential;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OutboundCredentialPolicy
{
    use HandlesAuthorization;

    public function manage(User $user, ?OutboundCredential $credential = null): bool
    {
        return $user->isIctAdministrator() || $user->hasPermission('credentials.manage');
    }
}