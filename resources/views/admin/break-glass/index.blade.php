@extends('layouts.app')

@section('title', __('nav.break_glass'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('nav.break_glass') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-wrap">
            @php $access = \App\Models\BreakGlassAccess::with(['user', 'patient'])->where('status', 'active')->latest('granted_at')->paginate(25); @endphp
            @if ($access->isEmpty())
                <p class="muted">{{ __('break_glass.no_pending') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('break_glass.for_patient') }}</th>
                            <th>{{ __('break_glass.requested_by') }}</th>
                            <th>{{ __('break_glass.reason_code') }}</th>
                            <th>{{ __('break_glass.granted_at') }}</th>
                            <th>{{ __('break_glass.expires_at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($access as $item)
                            <tr>
                                <td>{{ $item->patient?->full_name ?: '-' }}</td>
                                <td>{{ $item->user?->full_name ?: '-' }}</td>
                                <td>{{ $item->reason_code }}</td>
                                <td class="small">{{ $item->granted_at?->format(config('hms.clinical.dt_formats.datetime')) ?: '-' }}</td>
                                <td class="small">{{ $item->expires_at?->format(config('hms.clinical.dt_formats.datetime')) ?: '-' }}</td>
                                <td class="num">
                                    <a class="btn btn-sm btn-secondary" href="{{ route('admin.break-glass.show', $item) }}">{{ __('ui.view') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $access->links() }}
            @endif
        </div>
    </div>
@endsection