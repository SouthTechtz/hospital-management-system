<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Services\PatientAccessService;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function __construct(private readonly PatientAccessService $access)
    {
    }

    public function viewAny(User $user, object $encounter): bool
    {
        return $user->hasPermission('order.view')
            && $this->access->canAccess($user, $encounter->patient);
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasPermission('order.view')
            && $this->access->canAccess($user, $order->patient);
    }

    public function create(User $user, object $encounter): bool
    {
        if (! $user->hasPermission('order.create')) {
            return false;
        }

        if ($user->hasPermission('patient.view.any')) {
            return true;
        }

        return $encounter->clinician_id === $user->getKey()
            || $encounter->department_id === $user->department_id;
    }

    public function void(User $user, Order $order): bool
    {
        if (! $user->hasPermission('order.void')) {
            return false;
        }

        return $order->ordered_by === $user->getKey()
            || $user->hasPermission('patient.view.any');
    }
}