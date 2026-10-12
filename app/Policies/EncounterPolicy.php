<?php

namespace App\Policies;

use App\Models\Encounter;
use App\Models\User;
use App\Services\PatientAccessService;
use Illuminate\Auth\Access\HandlesAuthorization;

class EncounterPolicy
{
    use HandlesAuthorization;

    public function __construct(private readonly PatientAccessService $access)
    {
    }

    public function view(User $user, Encounter $encounter): bool
    {
        if (! $user->hasPermission('encounter.view')) {
            return false;
        }

        return $this->access->canAccess($user, $encounter->patient);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('encounter.create');
    }

    public function update(User $user, Encounter $encounter): bool
    {
        if (! $user->hasPermission('encounter.update')) {
            return false;
        }

        if ($user->hasPermission('encounter.view.any') || $user->hasPermission('patient.view.any')) {
            return true;
        }

        return $encounter->clinician_id === $user->getKey()
            || $encounter->department_id === $user->department_id;
    }

    public function close(User $user, Encounter $encounter): bool
    {
        return $this->update($user, $encounter) && $encounter->ended_at === null;
    }
}