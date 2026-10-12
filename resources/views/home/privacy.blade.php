@extends('layouts.app')

@section('title', __('home.pending_reviews'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('home.pending_reviews') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-wrap">
            @if ($pendingReviews->isEmpty())
                <p class="muted">{{ __('home.no_reviews') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('break_glass.requested_by') }}</th>
                            <th>{{ __('break_glass.for_patient') }}</th>
                            <th>{{ __('break_glass.reason_code') }}</th>
                            <th>{{ __('break_glass.granted_at') }}</th>
                            <th>{{ __('break_glass.expires_at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingReviews as $access)
                            <tr>
                                <td>{{ $access->user?->full_name }}</td>
                                <td>
                                    {{ $access->patient?->full_name }}
                                    <div class="small muted mono">{{ $access->patient?->mrn }}</div>
                                </td>
                                <td><span class="tag tag-warning">{{ $access->reason_code }}</span></td>
                                <td class="small">{{ $access->granted_at?->format(config('hms.clinical.dt_formats.datetime')) }}</td>
                                <td class="small">{{ $access->expires_at?->format(config('hms.clinical.dt_formats.datetime')) }}</td>
                                <td class="num">
                                    <a class="btn btn-sm btn-secondary" href="{{ route('admin.break-glass.show', $access) }}">{{ __('break_glass.review') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection