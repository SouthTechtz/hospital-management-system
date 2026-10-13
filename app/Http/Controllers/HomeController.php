<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Enums\OrderStatus;
use App\Models\CareAssignment;
use App\Models\Encounter;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\QueueEntry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Role home screens are work queues, not decorative dashboards (Part D7.2).
 */
class HomeController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $role = $user->primaryRole?->name
            ?? $user->roles()->first()?->name
            ?? 'administrator';

        return $this->renderHome($role, $user);
    }

    public function ajaxStats(Request $request)
    {
        $this->authorize('viewAny', User::class);
        $data = [
            'users' => User::where('is_active', true)->count(),
            'patients' => \App\Models\Patient::count(),
            'openEncounters' => Encounter::where('status', 'open')->count(),
            'openInvoices' => Invoice::whereIn('status', [InvoiceStatus::Open->value, InvoiceStatus::PartiallyPaid->value])->count(),
            'waiting' => \App\Models\QueueEntry::whereNull('served_at')->count(),
            'pendingBreakGlass' => \App\Models\BreakGlassAccess::where('status', 'active')->count(),
            'failures' => DB::table('failed_jobs')->count(),
        ];
        return response()->json($data);
    }

    private function renderHome(string $role, User $user)
    {
        return match ($role) {
            'opd_doctor', 'medical_officer' => view('home.doctor', [
                'consultationQueue' => $this->doctorQueue($user),
                'pendingOrders' => $this->pendingOrdersFor($user, 10),
                'openEncounters' => $this->openEncountersFor($user, 10),
            ]),
            'triage_nurse' => view('home.triage', [
                'waiting' => QueueEntry::with('patient')
                    ->whereHas('queue', fn ($q) => $q->where('type', 'triage'))
                    ->waiting()
                    ->orderBy('priority')
                    ->orderBy('added_at')
                    ->limit(50)
                    ->get(),
            ]),
            'ward_nurse' => view('home.ward', [
                'assigned' => CareAssignment::with('patient')
                    ->where('user_id', $user->getKey())
                    ->whereNull('released_at')
                    ->limit(40)
                    ->get(),
            ]),
            'laboratory_technologist' => view('home.empty-role', [
                'module' => 'laboratory',
            ]),
            'radiologist' => view('home.empty-role', [
                'module' => 'radiology',
            ]),
            'pharmacist' => view('home.pharmacy', [
                'prescriptions' => Order::with(['items.drug', 'patient', 'orderedBy'])
                    ->where('order_type', 'medication')
                    ->where('status', OrderStatus::Ordered->value)
                    ->whereNull('voided_at')
                    ->latest('ordered_at')
                    ->limit(40)
                    ->get(),
            ]),
            'cashier' => view('home.cashier', [
                'openInvoices' => Invoice::with('patient')
                    ->whereIn('status', [InvoiceStatus::Open->value, InvoiceStatus::PartiallyPaid->value])
                    ->latest()
                    ->limit(40)
                    ->get(),
            ]),
            'records_officer' => view('home.records', [
                'recentRegistrations' => \App\Models\Patient::with('facility')
                    ->latest()
                    ->limit(20)
                    ->get(),
            ]),
            'privacy_officer' => view('home.privacy', [
                'pendingReviews' => \App\Models\BreakGlassAccess::with('patient', 'user')
                    ->where('status', 'active')
                    ->latest('granted_at')
                    ->limit(25)
                    ->get(),
            ]),
            default => view('home.administrator', [
                'users' => User::with('roles')->where('is_active', true)->count(),
                'totalUsers' => User::count(),
                'inactiveUsers' => User::where('is_active', false)->count(),
                'patients' => \App\Models\Patient::count(),
                'recentPatients' => \App\Models\Patient::with('facility')->latest()->limit(10)->get(),
                'openEncounters' => Encounter::where('status', 'open')->count(),
                'totalEncounters' => Encounter::count(),
                'openInvoices' => Invoice::whereIn('status', ['open', 'partially_paid'])->count(),
                'paidInvoices' => Invoice::where('status', InvoiceStatus::Paid->value)->count(),
                'totalInvoices' => Invoice::count(),
                'failures' => DB::table('failed_jobs')->count(),
                'pendingBreakGlass' => \App\Models\BreakGlassAccess::where('status', 'active')->count(),
                'latestAudit' => \App\Models\AuditLog::latest('id')->limit(15)->get(),
                'latestOrders' => Order::with('patient')->latest('ordered_at')->limit(10)->get(),
            ]),
        };
    }

    private function doctorQueue(User $user)
    {
        $departmentId = $user->department_id;

        $queue = \App\Models\Queue::query()
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->where('type', 'clinical')
            ->first();

        if ($queue === null) {
            return collect();
        }

        return $queue->entries()
            ->with('patient')
            ->waiting()
            ->orderBy('priority')
            ->orderBy('added_at')
            ->limit(30)
            ->get();
    }

    private function pendingOrdersFor(User $user, int $limit)
    {
        return Order::with(['patient'])
            ->when($user->department_id, fn ($q) => $q->whereHas('encounter', fn ($e) => $e->where('department_id', $user->department_id)))
            ->where('status', OrderStatus::Ordered->value)
            ->whereNull('voided_at')
            ->latest('ordered_at')
            ->limit($limit)
            ->get();
    }

    private function openEncountersFor(User $user, int $limit)
    {
        return Encounter::with('patient')
            ->where('status', 'open')
            ->when($user->department_id, fn ($q) => $q->where('department_id', $user->department_id))
            ->latest()
            ->limit($limit)
            ->get();
    }
}