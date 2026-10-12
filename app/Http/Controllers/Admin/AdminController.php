<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BreakGlassAccess;
use App\Models\Encounter;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        return view('admin.dashboard', [
            'userCount' => User::with('roles')->count(),
            'activeUserCount' => User::where('is_active', true)->count(),
            'patientCount' => Patient::count(),
            'openEncounterCount' => Encounter::where('status', 'open')->count(),
            'openInvoiceCount' => Invoice::whereIn('status', ['open', 'partially_paid'])->count(),
            'failedJobCount' => \Illuminate\Support\Facades\DB::table('failed_jobs')->count(),
            'breakGlassPending' => BreakGlassAccess::where('status', 'active')->count(),
            'latestAudit' => AuditLog::latest('id')->limit(20)->get(),
        ]);
    }
}