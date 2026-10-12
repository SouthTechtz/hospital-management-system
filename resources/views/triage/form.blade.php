@extends('layouts.app')

@section('title', __('triage.assess'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('triage.assess') }}</h1>
            <p class="muted small">{{ $encounter->patient->full_name }} - <span class="mono">{{ $encounter->patient->mrn }}</span></p>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('encounters.show', $encounter) }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('triage.store') }}" class="stack">
                @csrf
                <input type="hidden" name="encounter_id" value="{{ $encounter->getKey() }}">

                <div class="field-grid">
                    <div class="form-field">
                        <label class="form-label" for="category">{{ __('triage.category') }}</label>
                        <select class="select" id="category" name="category" required>
                            <option value="">{{ __('ui.select_default') }}</option>
                            @foreach (\App\Enums\TriageCategory::cases() as $cat)
                                <option value="{{ $cat->value }}">{{ __($cat->labelKey()) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="chief_complaint">{{ __('triage.chief_complaint') }}</label>
                        <textarea class="textarea" id="chief_complaint" name="chief_complaint" rows="2"></textarea>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="notes">{{ __('triage.notes') }}</label>
                        <textarea class="textarea" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </div>

                <h2 class="section-title">{{ __('triage.vitals') }}</h2>
                <div class="field-grid">
                    <div class="form-field">
                        <label class="form-label" for="vitals_temperature_c">{{ __('triage.temperature') }}</label>
                        <input class="input" id="vitals_temperature_c" name="vitals[temperature_c]" type="number" step="0.1">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="vitals_pulse">{{ __('triage.pulse') }}</label>
                        <input class="input" id="vitals_pulse" name="vitals[pulse]" type="number">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="vitals_respiratory_rate">{{ __('triage.respiratory_rate') }}</label>
                        <input class="input" id="vitals_respiratory_rate" name="vitals[respiratory_rate]" type="number">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="vitals_systolic">{{ __('triage.systolic') }}</label>
                        <input class="input" id="vitals_systolic" name="vitals[systolic]" type="number">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="vitals_diastolic">{{ __('triage.diastolic') }}</label>
                        <input class="input" id="vitals_diastolic" name="vitals[diastolic]" type="number">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="vitals_spo2">{{ __('triage.spo2') }}</label>
                        <input class="input" id="vitals_spo2" name="vitals[spo2]" type="number">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="vitals_weight_kg">{{ __('triage.weight') }}</label>
                        <input class="input" id="vitals_weight_kg" name="vitals[weight_kg]" type="number" step="0.1">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="vitals_height_cm">{{ __('triage.height') }}</label>
                        <input class="input" id="vitals_height_cm" name="vitals[height_cm]" type="number" step="0.1">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="vitals_muac_mm">{{ __('triage.muac') }}</label>
                        <input class="input" id="vitals_muac_mm" name="vitals[muac_mm]" type="number">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="vitals_pain_score">{{ __('triage.pain_score') }}</label>
                        <input class="input" id="vitals_pain_score" name="vitals[pain_score]" type="number">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="vitals_gcs">{{ __('triage.gcs') }}</label>
                        <input class="input" id="vitals_gcs" name="vitals[gcs]" type="number">
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="vitals_notes">{{ __('triage.vitals_notes') }}</label>
                        <textarea class="textarea" id="vitals_notes" name="vitals[notes]" rows="2"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">{{ __('triage.save_triage') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection