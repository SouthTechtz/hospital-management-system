<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;
use App\Services\PatientAccessService;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConsultationPolicy
{
    use HandlesAuthorization;

    public function __construct(private readonly PatientAccessService $access)
    {
    }

    public function viewAny(User $user, object $encounter): bool
    {
        return $user->hasPermission('consultation.view')
            && $this->access->canAccess($user, $encounter->patient);
    }

    public function view(User $user, Consultation $consultation): bool
    {
        return $user->hasPermission('consultation.view')
            && $this->access->canAccess($user, $consultation->patient);
    }

    public function create(User $user, object $encounter): bool
    {
        if (! $user->hasPermission('consultation.write')) {
            return false;
        }

        if ($user->hasPermission('patient.view.any')) {
            return true;
        }

        return $encounter->clinician_id === $user->getKey()
            || $encounter->department_id === $user->department_id;
    }

    public function update(User $user, Consultation $consultation): bool
    {
        return $this->create($user, $consultation->encounter);
    }
}