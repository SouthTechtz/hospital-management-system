<?php

namespace App\Services;

use App\Enums\BreakGlassStatus;
use App\Models\BreakGlassAccess;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Object-level access decisions for patient records (Part B). A user may open
 * a record only when a care relationship exists, an active break-glass grant
 * exists, or they hold the explicit administrative permission
 * `patient.view.any`.
 */
class PatientAccessService
{
    public function canAccess(User $user, Patient $patient): bool
    {
        if ($user->hasPermission('patient.view.any')) {
            return true;
        }

        return $this->hasCareRelationship($user, $patient)
            || $this->hasActiveBreakGlass($user, $patient);
    }

    public function hasCareRelationship(User $user, Patient $patient): bool
    {
        $hasAssignment = DB::table('care_assignments')
            ->where('patient_id', $patient->getKey())
            ->whereNull('released_at')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->getKey());
                if ($user->department_id) {
                    $query->orWhere('department_id', $user->department_id);
                }
            })
            ->exists();

        if ($hasAssignment) {
            return true;
        }

        // Encountered by the user or their unit.
        return DB::table('encounters')
            ->where('patient_id', $patient->getKey())
            ->whereNull('deleted_at')
            ->where(function ($query) use ($user) {
                $query->where('clinician_id', $user->getKey());
                if ($user->department_id) {
                    $query->orWhere('department_id', $user->department_id);
                }
            })
            ->exists();
    }

    public function hasActiveBreakGlass(User $user, Patient $patient): bool
    {
        return BreakGlassAccess::query()
            ->where('user_id', $user->getKey())
            ->where('patient_id', $patient->getKey())
            ->where('status', BreakGlassStatus::Active->value)
            ->where('expires_at', '>', now())
            ->exists();
    }
}
