@extends('layouts.app')

@section('title', __('home.title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('home.title') }}</h1>
        </div>
    </div>

    <div class="grid grid-4 mb-4">
        <div class="stat">
            <div class="stat-value">{{ $users }}</div>
            <div class="stat-label">{{ __('home.users') }}</div>
        </div>
        <div class="stat">
            <div class="stat-value">{{ $patients }}</div>
            <div class="stat-label">{{ __('home.patients') }}</div>
        </div>
        <div class="stat">
            <div class="stat-value">{{ $openEncounters }}</div>
            <div class="stat-label">{{ __('home.open_encounters') }}</div>
        </div>
        <div class="stat">
            <div class="stat-value">{{ $openInvoices }}</div>
            <div class="stat-label">{{ __('home.open_invoices') }}</div>
        </div>
    </div>

    @if ($failures > 0)
        <div class="alert alert-warning" role="status">
            {{ __('home.failed_jobs') }}: {{ $failures }}
        </div>
    @endif

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