@extends('layouts.app')

@section('title', __('patients.view_break_glass'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('patients.view_break_glass') }}</h1>
            <p class="muted small">{{ $patient->full_name }} - <span class="mono">{{ $patient->mrn }}</span></p>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('patients.show', $patient) }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    @if ($currentAccess)
        <div class="alert alert-danger" role="status">
            {{ __('patients.break_glass_active', ['expires' => $currentAccess->expires_at?->format(config('hms.clinical.dt_formats.datetime')) ?: '-']) }}
        </div>
    @else
        <div class="alert alert-warning" role="status">{{ __('patients.break_glass_explainer') }}</div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('patients.break-glass.store') }}" class="stack">
                    @csrf
                    <input type="hidden" name="patient_id" value="{{ $patient->getKey() }}">
                    <div class="form-field">
                        <label class="form-label" for="reason_code">{{ __('break_glass.reason_code') }}</label>
                        <select class="select" id="reason_code" name="reason_code" required>
                            <option value="">{{ __('ui.select_default') }}</option>
                            @foreach ([
                                'emergency_care' => __('break_glass.reason_emergency_care'),
                                'unassigned_emergency' => __('break_glass.reason_unassigned_emergency'),
                                'patient_unresponsive' => __('break_glass.reason_patient_unresponsive'),
                                'audit_request' => __('break_glass.reason_audit_request'),
                                'other' => __('break_glass.reason_other'),
                            ] as $code => $label)
                                <option value="{{ $code }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="reason_text">{{ __('patients.break_glass_reason_text') }}</label>
                        <textarea class="textarea" id="reason_text" name="reason_text" rows="3" required
                                  placeholder="{{ __('patients.break_glass_reason_hint') }}"></textarea>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">{{ __('patients.request_break_glass') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection