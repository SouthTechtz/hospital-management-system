<?php

namespace App\Policies;

use App\Models\Facility;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FacilityPolicy
{
    use HandlesAuthorization;

    public function manage(User $user, ?Facility $facility = null): bool
    {
        return $user->hasPermission('facility.manage') || $user->isIctAdministrator();
    }
}