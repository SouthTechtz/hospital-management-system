<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;
use App\Services\PatientAccessService;
use Illuminate\Auth\Access\HandlesAuthorization;

class PatientPolicy
{
    use HandlesAuthorization;

    public function __construct(private readonly PatientAccessService $access)
    {
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['patient.view', 'patient.search', 'patient.view.any']);
    }

    public function view(User $user, Patient $patient): bool
    {
        if (! $user->hasPermission('patient.view') && ! $user->hasPermission('patient.view.any')) {
            return false;
        }

        return $this->access->canAccess($user, $patient);
    }

    /**
     * The automatic deny for the care-relationship test must never leak into
     * registration (creating a new patient is not opening their record).
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('patient.register');
    }

    public function search(User $user): bool
    {
        return $user->hasAnyPermission(['patient.search', 'patient.view.any']);
    }

    public function update(User $user, Patient $patient): bool
    {
        if (! $user->hasPermission('patient.update')) {
            return false;
        }

        return $user->hasPermission('patient.view.any') || $this->access->canAccess($user, $patient);
    }

    public function merge(User $user, ?Patient $patient = null): bool
    {
        return $user->hasPermission('patient.merge');
    }

    public function breakGlass(User $user): bool
    {
        return $user->hasPermission('patient.break_glass');
    }

    public function manageAllergies(User $user, Patient $patient): bool
    {
        if (! $user->hasPermission('patient.allergy.manage')) {
            return false;
        }

        return $user->hasPermission('patient.view.any') || $this->access->canAccess($user, $patient);
    }

    public function delete(User $user, Patient $patient): bool
    {
        // Records are soft-deleted at most; hard delete is restricted to
        // system roles and always logged via the model.
        return $user->hasPermission('patient.delete');
    }
}