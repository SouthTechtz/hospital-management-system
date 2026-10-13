@extends('layouts.app')

@section('title', __('home.title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('home.title') }}</h1>
        </div>
    </div>

    <div class="grid grid-4 mb-4" data-dashboard-stats="{{ route('home.ajax') }}">
        <div class="stat">
            <div class="stat-value">{{ $users }}</div>
            <div class="stat-label">{{ __('home.users') }}</div>
            <div class="small muted">{{ __('home.total') ?? 'Total' }}: {{ $totalUsers }} | {{ __('admin.inactive') ?? 'Inactive' }}: {{ $inactiveUsers }}</div>
        </div>
        <div class="stat">
            <div class="stat-value">{{ $patients }}</div>
            <div class="stat-label">{{ __('home.patients') }}</div>
        </div>
        <div class="stat">
            <div class="stat-value">{{ $openEncounters }}</div>
            <div class="stat-label">{{ __('home.open_encounters') }}</div>
            <div class="small muted">{{ __('home.total') ?? 'Total' }}: {{ $totalEncounters }}</div>
        </div>
        <div class="stat">
            <div class="stat-value">{{ $openInvoices }}</div>
            <div class="stat-label">{{ __('home.open_invoices') }}</div>
            <div class="small muted">{{ __('home.paid') ?? 'Paid' }}: {{ $paidInvoices }} | {{ __('home.total') ?? 'Total' }}: {{ $totalInvoices }}</div>
        </div>
    </div>

    @if ($failures > 0)
        <div class="alert alert-warning" role="status">
            {{ __('home.failed_jobs') }}: {{ $failures }}
        </div>
    @endif

    @if ($pendingBreakGlass > 0 && auth()->user()->can('breakglass.review'))
        <div class="alert alert-danger" role="status">
            <strong>{{ __('home.break_glass_pending') }}</strong>: {{ $pendingBreakGlass }} &nbsp; <a href="{{ route('admin.break-glass.index') }}" class="btn btn-sm btn-secondary">{{ __('ui.review') ?? 'Review' }}</a>
        </div>
    @endif

    <div class="grid grid-2">
        <div class="card">
            <div class="card-header">
                <h2>{{ __('home.recent_registrations') }}</h2>
                <div class="toolbar">
                    @can('patient.create')
                        <a href="{{ route('patients.create') }}" class="btn btn-sm btn-primary">{{ __('patients.register') }}</a>
                    @endcan
                </div>
            </div>
            <div class="card-body table-wrap">
                @if ($recentPatients->isEmpty())
                    <p class="muted">{{ __('home.no_registrations') }}</p>
                @else
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('patients.name') }}</th>
                                <th>{{ __('patients.mrn') }}</th>
                                <th>{{ __('patients.facility') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentPatients as $patient)
                                <tr>
                                    <td>{{ $patient->full_name }}</td>
                                    <td class="mono small">{{ $patient->mrn }}</td>
                                    <td class="small">{{ $patient->facility?->name }}</td>
                                    <td class="num">
                                        @can('patient.view', $patient)
                                            <a class="btn btn-sm btn-secondary" href="{{ route('patients.show', $patient) }}">{{ __('ui.view') }}</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>{{ __('home.latest_orders') }}</h2>
            </div>
            <div class="card-body table-wrap">
                @if ($latestOrders->isEmpty())
                    <p class="muted">{{ __('home.no_pending_orders') }}</p>
                @else
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('triage.patient') }}</th>
                                <th>{{ __('orders.type') }}</th>
                                <th>{{ __('ui.status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($latestOrders as $order)
                                <tr>
                                    <td>{{ $order->patient->full_name }}<div class="small muted mono">{{ $order->patient->mrn }}</div></td>
                                    <td>{{ __($order->order_type->labelKey()) }}</td>
                                    <td><span class="tag tag-neutral">{{ __($order->status->labelKey()) }}</span></td>
                                    <td class="num">
                                        @can('order.view', $order)
                                            <a class="btn btn-sm btn-secondary" href="{{ route('orders.show', $order) }}">{{ __('ui.view') }}</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>{{ __('home.latest_audit') }}</h2>
        </div>
        <div class="card-body table-wrap">
            @if ($latestAudit->isEmpty())
                <p class="muted">{{ __('home.no_audit_events') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('admin.occurred_at') }}</th>
                            <th>{{ __('admin.actor') }}</th>
                            <th>{{ __('admin.action') }}</th>
                            <th>{{ __('admin.description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($latestAudit as $log)
                            <tr>
                                <td class="small">{{ $log->occurred_at?->format(config('hms.clinical.dt_formats.datetime')) }}</td>
                                <td>{{ $log->actor_name ?: '-' }}</td>
                                <td><span class="tag tag-neutral">{{ $log->action->labelKey() ? __($log->action->labelKey()) : $log->action->value }}</span></td>
                                <td class="small">{{ $log->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection