@extends('layouts.app')

@section('title', __('encounters.title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('encounters.title') }} - {{ $encounter->visit_number }}</h1>
            <p class="muted small">
                {{ $encounter->patient->full_name }} - <span class="mono">{{ $encounter->patient->mrn }}</span>
            </p>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('patients.show', $encounter->patient) }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    <div class="card-grid">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('ui.details') }}</h2>
            </div>
            <div class="card-body">
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt>{{ __('encounters.type') }}</dt>
                        <dd>{{ __($encounter->encounter_type->labelKey()) }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('encounters.status') }}</dt>
                        <dd>{{ __($encounter->status->labelKey()) }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('encounters.department') }}</dt>
                        <dd>{{ $encounter->department?->name ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('encounters.clinician') }}</dt>
                        <dd>{{ $encounter->clinician?->full_name ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('encounters.location') }}</dt>
                        <dd>{{ $encounter->location ?: '-' }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('encounters.payment_type') }}</dt>
                        <dd>{{ __($encounter->payment_type->labelKey()) }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('encounters.started_at') }}</dt>
                        <dd>{{ $encounter->started_at?->format(config('hms.clinical.dt_formats.datetime')) ?: '-' }}</dd>
                    </div>
                    @if ($encounter->ended_at)
                        <div class="detail-row">
                            <dt>{{ __('encounters.ended_at') }}</dt>
                            <dd>{{ $encounter->ended_at->format(config('hms.clinical.dt_formats.datetime')) }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
            @if ($encounter->status->value === 'open')
                <div class="card-body">
                    <form method="POST" action="{{ route('encounters.close', $encounter) }}">
                        @csrf
                        <button class="btn btn-secondary" type="submit">{{ __('encounters.close_encounter') }}</button>
                    </form>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('triage.title') }}</h2>
            </div>
            <div class="card-body">
                @if ($encounter->triage)
                    <dl class="detail-list">
                        <div class="detail-row">
                            <dt>{{ __('triage.category') }}</dt>
                            <dd><span class="tag triage-{{ $encounter->triage->triage_category->value }}">{{ __($encounter->triage->triage_category->labelKey()) }}</span></dd>
                        </div>
                        <div class="detail-row">
                            <dt>{{ __('triage.chief_complaint') }}</dt>
                            <dd>{{ $encounter->triage->chief_complaint ?: '-' }}</dd>
                        </div>
                        <div class="detail-row">
                            <dt>{{ __('triage.priority') }}</dt>
                            <dd>{{ $encounter->triage->priority }}</dd>
                        </div>
                        @if ($encounter->triage->vital_signs)
                            <div class="detail-row">
                                <dt>{{ __('triage.vitals') }}</dt>
                                <dd>
                                    @if ($encounter->triage->vital_signs->temperature)
                                        <span class="pill">{{ __('triage.temperature') }}: {{ $encounter->triage->vital_signs->temperature }}</span>
                                    @endif
                                    @if ($encounter->triage->vital_signs->pulse)
                                        <span class="pill">{{ __('triage.pulse') }}: {{ $encounter->triage->vital_signs->pulse }}</span>
                                    @endif
                                    @if ($encounter->triage->vital_signs->blood_pressure_systolic)
                                        <span class="pill">{{ __('triage.blood_pressure') }}: {{ $encounter->triage->vital_signs->blood_pressure_systolic }}/{{ $encounter->triage->vital_signs->blood_pressure_diastolic }}</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                @else
                    <p class="muted">{{ __('encounters.no_triage') }}</p>
                    <a class="btn btn-secondary btn-sm" href="{{ route('triage.form', $encounter) }}">{{ __('triage.assess') }}</a>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('consultations.title') }}</h2>
            @if ($encounter->status->value === 'open')
                <a class="btn btn-primary btn-sm" href="{{ route('consultations.show', $encounter) }}">{{ __('encounters.consultation_link') }}</a>
            @endif
        </div>
        <div class="card-body">
            @php $consultation = $encounter->consultations->first(); @endphp
            @if (! $consultation)
                <p class="muted">{{ __('encounters.no_consultation') }}</p>
            @else
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt>{{ __('ui.date') }}</dt>
                        <dd>{{ $consultation->encounter_date?->format(config('hms.clinical.dt_formats.date')) ?: '-' }}</dd>
                    </div>
                    @if ($consultation->presenting_complaint)
                        <div class="detail-row">
                            <dt>{{ __('consultations.history') }}</dt>
                            <dd>{{ $consultation->presenting_complaint }}</dd>
                        </div>
                    @endif
                    @if ($consultation->examination_findings)
                        <div class="detail-row">
                            <dt>{{ __('consultations.examination') }}</dt>
                            <dd>{{ $consultation->examination_findings }}</dd>
                        </div>
                    @endif
                    @if ($consultation->plan)
                        <div class="detail-row">
                            <dt>{{ __('consultations.plan') }}</dt>
                            <dd>{{ $consultation->plan }}</dd>
                        </div>
                    @endif
                </dl>
            @endif
        </div>
    </div>
@endsection