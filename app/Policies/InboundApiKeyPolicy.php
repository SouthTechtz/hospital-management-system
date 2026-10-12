<?php

namespace App\Policies;

use App\Models\InboundApiKey;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InboundApiKeyPolicy
{
    use HandlesAuthorization;

    public function manage(User $user, ?InboundApiKey $key = null): bool
    {
        return $user->isIctAdministrator() || $user->hasPermission('apikey.manage');
    }
}