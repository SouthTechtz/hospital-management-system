@extends('layouts.app')

@section('title', __('admin.break_glass'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.break_glass') }}</h1>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('admin.break-glass.index') }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="detail-list">
                <div class="detail-row">
                    <dt>{{ __('break_glass.for_patient') }}</dt>
                    <dd>{{ $access->patient?->full_name ?: '-' }}</dd>
                </div>
                <div class="detail-row">
                    <dt>{{ __('break_glass.requested_by') }}</dt>
                    <dd>{{ $access->user?->full_name ?: '-' }}</dd>
                </div>
                <div class="detail-row">
                    <dt>{{ __('break_glass.reason_code') }}</dt>
                    <dd>{{ $access->reason_code }}</dd>
                </div>
                <div class="detail-row">
                    <dt>{{ __('break_glass.granted_at') }}</dt>
                    <dd>{{ $access->granted_at?->format(config('hms.clinical.dt_formats.datetime')) ?: '-' }}</dd>
                </div>
                <div class="detail-row">
                    <dt>{{ __('break_glass.expires_at') }}</dt>
                    <dd>{{ $access->expires_at?->format(config('hms.clinical.dt_formats.datetime')) ?: '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection