@extends('layouts.app')

@section('title', __('consultations.title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('consultations.title') }}</h1>
            <p class="muted small">
                {{ $encounter->patient->full_name }} - <span class="mono">{{ $encounter->patient->mrn }}</span>
                ({{ __('encounters.visit') }} {{ $encounter->visit_number }})
            </p>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('encounters.show', $encounter) }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    @if ($encounter->patient->activeAllergies->isNotEmpty())
        @foreach ($encounter->patient->activeAllergies as $allergy)
            <div class="alert alert-danger" role="status">
                {{ __('patients.active_allergy_banner', ['substance' => $allergy->substance, 'reaction' => $allergy->reaction ?: __('ui.unknown')]) }}
            </div>
        @endforeach
    @endif

    <form method="POST" action="{{ route('consultations.store', $encounter) }}" class="stack">
        @csrf

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('consultations.history') }}</h2>
            </div>
            <div class="card-body">
                <div class="form-field">
                    <label class="form-label" for="presenting_complaint">{{ __('consultations.history') }}</label>
                    <textarea class="textarea" id="presenting_complaint" name="presenting_complaint" rows="3"
                              placeholder="{{ __('consultations.history_hint') }}"></textarea>
                </div>
                <div class="form-field">
                    <label class="form-label" for="history_of_present_illness">{{ __('consultations.history') }}</label>
                    <textarea class="textarea" id="history_of_present_illness" name="history_of_present_illness" rows="3"></textarea>
                </div>
                <div class="form-field">
                    <label class="form-label" for="past_medical_history">{{ __('patients.notes') }}</label>
                    <textarea class="textarea" id="past_medical_history" name="past_medical_history" rows="2"></textarea>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('consultations.examination') }}</h2>
            </div>
            <div class="card-body">
                <textarea class="textarea" name="examination_findings" rows="4"
                          placeholder="{{ __('consultations.examination_hint') }}"></textarea>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('consultations.diagnoses') }}</h2>
            </div>
            <div class="card-body">
                <div data-row-scaffold="#diagnosis-rows" data-add-row="#diagnosis-add" data-row-template="diagnosis-template">
                    <div id="diagnosis-rows" class="stack"></div>
                    <div class="row">
                        <button type="button" class="btn btn-secondary btn-sm" id="diagnosis-add">{{ __('consultations.add_diagnosis') }}</button>
                    </div>
                </div>
                <template id="diagnosis-template">
                    <div class="diagnosis-row" data-row>
                        <div class="field-grid">
                            <div class="form-field">
                                <label class="form-label">{{ __('consultations.icd10') }}</label>
                                <input class="input" type="text" name="diagnoses[__INDEX__][icd10_code]" placeholder="{{ __('consultations.icd10_search') }}" data-autocomplete="{{ route('lookup.icd10') }}">
                                <input type="hidden" name="diagnoses[__INDEX__][description]" value="">
                            </div>
                            <div class="form-field">
                                <label class="form-label">{{ __('consultations.diagnosis_type') }}</label>
                                <select class="select" name="diagnoses[__INDEX__][type]">
                                    @foreach (\App\Enums\DiagnosisType::cases() as $type)
                                        <option value="{{ $type->value }}">{{ __($type->labelKey()) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-field">
                                <label class="form-label">{{ __('consultations.diagnosis_notes') }}</label>
                                <input class="input" type="text" name="diagnoses[__INDEX__][notes]">
                            </div>
                        </div>
                        <div class="row">
                            <button type="button" class="btn btn-secondary btn-sm" data-remove-row>{{ __('ui.remove') }}</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('consultations.orders') }}</h2>
            </div>
            <div class="card-body">
                <div class="field-grid">
                    <div class="form-field">
                        <label class="form-label" for="order_priority">{{ __('consultations.order_priority') }}</label>
                        <select class="select" id="order_priority" name="order_priority">
                            @foreach (\App\Enums\OrderPriority::cases() as $priority)
                                <option value="{{ $priority->value }}">{{ __($priority->labelKey()) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="order_notes">{{ __('consultations.order_notes') }}</label>
                        <textarea class="textarea" id="order_notes" name="order_notes" rows="2"></textarea>
                    </div>
                </div>

                <div data-row-scaffold="#order-rows" data-add-row="#order-add" data-row-template="order-template">
                    <div id="order-rows" class="stack"></div>
                    <div class="row">
                        <button type="button" class="btn btn-secondary btn-sm" id="order-add">{{ __('consultations.add_order') }}</button>
                    </div>
                </div>
                <template id="order-template">
                    <div class="order-row" data-row>
                        <div class="field-grid">
                            <div class="form-field">
                                <label class="form-label">{{ __('consultations.order_drug') }}</label>
                                <input class="input" type="text" name="order_items[__INDEX__][drug_label]" placeholder="{{ __('consultations.order_drug_search') }}" data-autocomplete="{{ route('lookup.drugs') }}">
                                <input type="hidden" name="order_items[__INDEX__][drug_id]" value="">
                                <input type="hidden" name="order_items[__INDEX__][description]" value="">
                            </div>
                            <div class="form-field">
                                <label class="form-label">{{ __('consultations.order_dose') }}</label>
                                <input class="input" type="text" name="order_items[__INDEX__][dose]">
                            </div>
                            <div class="form-field">
                                <label class="form-label">{{ __('consultations.order_dose_unit') }}</label>
                                <input class="input" type="text" name="order_items[__INDEX__][dose_unit]">
                            </div>
                            <div class="form-field">
                                <label class="form-label">{{ __('consultations.order_route') }}</label>
                                <input class="input" type="text" name="order_items[__INDEX__][route]">
                            </div>
                            <div class="form-field">
                                <label class="form-label">{{ __('consultations.order_frequency') }}</label>
                                <input class="input" type="text" name="order_items[__INDEX__][frequency]">
                            </div>
                            <div class="form-field">
                                <label class="form-label">{{ __('consultations.order_duration') }}</label>
                                <input class="input" type="number" name="order_items[__INDEX__][duration_days]">
                            </div>
                            <div class="form-field">
                                <label class="form-label">{{ __('consultations.order_quantity') }}</label>
                                <input class="input" type="number" step="0.1" name="order_items[__INDEX__][quantity]">
                            </div>
                            <div class="form-field">
                                <label class="form-label">{{ __('consultations.order_prescriber') }}</label>
                                <input class="input" type="text" name="order_items[__INDEX__][prescriber_notes]">
                            </div>
                        </div>
                        <div class="row">
                            <button type="button" class="btn btn-secondary btn-sm" data-remove-row>{{ __('ui.remove') }}</button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('consultations.plan') }}</h2>
            </div>
            <div class="card-body">
                <div class="form-field">
                    <label class="form-label" for="plan">{{ __('consultations.plan') }}</label>
                    <textarea class="textarea" id="plan" name="plan" rows="3"></textarea>
                </div>
                <div class="field-grid">
                    <div class="form-field">
                        <label class="form-label" for="disposition">{{ __('consultations.disposition') }}</label>
                        <select class="select" id="disposition" name="disposition">
                            <option value="">{{ __('ui.default') }}</option>
                            @foreach (\App\Enums\Disposition::cases() as $dispo)
                                <option value="{{ $dispo->value }}">{{ __($dispo->labelKey()) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="follow_up_date">{{ __('consultations.follow_up_date') }}</label>
                        <input class="input" id="follow_up_date" name="follow_up_date" type="date">
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-label" for="correction_reason">{{ __('consultations.correction_reason') }}</label>
                    <textarea class="textarea" id="correction_reason" name="correction_reason" rows="2"
                              placeholder="{{ __('consultations.correction_hint') }}"></textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">{{ __('consultations.save_consultation') }}</button>
        </div>
    </form>

    @if ($previousEncounters->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('consultations.previous_encounters') }}</h2>
            </div>
            <div class="card-body table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('encounters.visit') }}</th>
                            <th>{{ __('ui.date') }}</th>
                            <th>{{ __('consultations.diagnoses') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($previousEncounters as $prev)
                            <tr>
                                <td class="mono small">{{ $prev->visit_number }}</td>
                                <td class="small">{{ $prev->started_at?->format(config('hms.clinical.dt_formats.date')) ?: '-' }}</td>
                                <td class="small">
                                    @foreach ($prev->diagnoses as $diag)
                                        <span class="pill">{{ $diag->icd10_code }}</span>
                                    @endforeach
                                </td>
                                <td class="num">
                                    <a class="btn btn-sm btn-secondary" href="{{ route('encounters.show', $prev) }}">{{ __('ui.view') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection