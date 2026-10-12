@extends('layouts.app')

@section('title', __('admin.audit_title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.audit_title') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit.index') }}" class="filter-bar">
                <input class="input" name="patient" type="text" value="{{ request('patient') }}" placeholder="{{ __('admin.filter_patient') }}">
                <button class="btn btn-primary" type="submit">{{ __('admin.apply_filters') }}</button>
            </form>
        </div>
        <div class="card-body table-wrap">
            @php $logs = \App\Models\AuditLog::with(['actor', 'patient'])->latest('occurred_at')->paginate(25); @endphp
            @if ($logs->isEmpty())
                <p class="muted">{{ __('admin.audit_no_records') }}</p>
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
                        @foreach ($logs as $log)
                            <tr>
                                <td class="small">{{ $log->occurred_at?->format(config('hms.clinical.dt_formats.datetime')) ?: '-' }}</td>
                                <td>{{ $log->actor?->full_name ?: '-' }}</td>
                                <td class="mono small">{{ $log->action->value }}</td>
                                <td class="small">{{ $log->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $logs->links() }}
            @endif
        </div>
    </div>
@endsection