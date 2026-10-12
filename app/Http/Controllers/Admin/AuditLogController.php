<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditAction;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        $query = AuditLog::query()->with('user', 'patient')->latest('id');

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->query('user_id'));
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->query('patient_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('occurred_at', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('occurred_at', '<=', $request->query('to'));
        }

        return view('admin.audit.index', [
            'logs' => $query->paginate(50)->withQueryString(),
            'actions' => AuditAction::cases(),
            'filters' => $request->query(),
        ]);
    }

    /**
     * CSV export of the filtered set (audited; export is restricted to
     * permission holders and never includes redacted fields).
     */
    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', AuditLog::class);

        $query = AuditLog::query()->latest('id');

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('from')) {
            $query->whereDate('occurred_at', '>=', $request->query('from'));
        }

        $filename = 'audit-log-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'occurred_at', 'action', 'actor', 'role', 'facility_id', 'patient_id', 'auditable_type', 'auditable_id', 'description', 'ip_address', 'device']);

            $query->chunkById(500, function ($rows) use ($out) {
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row->id,
                        $row->occurred_at?->toDateTimeString(),
                        $row->action->value,
                        $row->actor_name,
                        $row->actor_role,
                        $row->facility_id,
                        $row->patient_id,
                        $row->auditable_type,
                        $row->auditable_id,
                        $row->description,
                        $row->ip_address,
                        $row->device,
                    ]);
                }
            }, 'id');

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}