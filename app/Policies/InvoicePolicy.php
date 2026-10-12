<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Services\PatientAccessService;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    public function __construct(private readonly PatientAccessService $access)
    {
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('invoice.view');
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $this->view($user, $invoice);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if (! $user->hasPermission('invoice.view')) {
            return false;
        }

        return $user->hasPermission('patient.view.any')
            || $this->access->hasCareRelationship($user, $invoice->patient);
    }

    public function create(User $user, object $encounterOrPatient): bool
    {
        return $user->hasPermission('invoice.create');
    }

    public function pay(User $user, ?Invoice $invoice = null): bool
    {
        return $user->hasPermission('invoice.pay');
    }

    public function void(User $user, ?Invoice $invoice = null): bool
    {
        return $user->hasPermission('invoice.void');
    }
}