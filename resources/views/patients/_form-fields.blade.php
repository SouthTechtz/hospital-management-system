@php
    $patient = $patient ?? null;
    $value = fn (string $key, $default = null) => old($key, $patient?->getAttribute($key) ?? $default);
    $consentKeys = [
        'treatment' => __('patients.consent_treatment'),
        'data_sharing' => __('patients.consent_data_sharing'),
        'research' => __('patients.consent_research'),
    ];
@endphp

<div class="field-grid">
    <div class="form-field {{ $errors->has('first_name') ? 'field-error' : '' }}">
        <label class="form-label" for="first_name">{{ __('patients.first_name') }} <span class="form-required">*</span></label>
        <input class="input" id="first_name" name="first_name" type="text" value="{{ $value('first_name') }}" required>
    </div>
    <div class="form-field {{ $errors->has('middle_name') ? 'field-error' : '' }}">
        <label class="form-label" for="middle_name">{{ __('patients.middle_name') }}</label>
        <input class="input" id="middle_name" name="middle_name" type="text" value="{{ $value('middle_name') }}">
    </div>
    <div class="form-field {{ $errors->has('last_name') ? 'field-error' : '' }}">
        <label class="form-label" for="last_name">{{ __('patients.last_name') }} <span class="form-required">*</span></label>
        <input class="input" id="last_name" name="last_name" type="text" value="{{ $value('last_name') }}" required>
    </div>
    <div class="form-field {{ $errors->has('sex') ? 'field-error' : '' }}">
        <label class="form-label" for="sex">{{ __('patients.sex') }} <span class="form-required">*</span></label>
        <select class="select" id="sex" name="sex" required>
            <option value="" @selected($value('sex') === '')>{{ __('ui.select_default') }}</option>
            @foreach (\App\Enums\Sex::cases() as $sex)
                <option value="{{ $sex->value }}" @selected($value('sex') === $sex->value)>{{ __($sex->labelKey()) }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-field {{ $errors->has('date_of_birth') ? 'field-error' : '' }}">
        <label class="form-label" for="date_of_birth">{{ __('patients.date_of_birth') }}</label>
        <input class="input" id="date_of_birth" name="date_of_birth" type="date"
               value="{{ $value('date_of_birth') }}" max="{{ now()->toDateString() }}">
    </div>
    <div class="form-field {{ $errors->has('estimated_age_years') ? 'field-error' : '' }}">
        <label class="form-label" for="estimated_age_years">{{ __('patients.estimated_age') }}</label>
        <input class="input" id="estimated_age_years" name="estimated_age_years" type="number" min="0" max="120"
               value="{{ $value('estimated_age_years') }}">
    </div>
    <div class="form-field checkbox-row">
        <input type="checkbox" id="birth_date_estimated" name="birth_date_estimated" value="1"
               @checked($value('birth_date_estimated'))>
        <label for="birth_date_estimated">{{ __('patients.birth_date_estimated') }}</label>
    </div>
</div>

<div class="section">
    <h3 class="section-title">{{ __('patients.identifiers') }}</h3>
    <div class="field-grid">
        <div class="form-field {{ $errors->has('national_id') ? 'field-error' : '' }}">
            <label class="form-label" for="national_id">{{ __('patients.national_id') }}</label>
            <input class="input" id="national_id" name="national_id" type="text" inputmode="numeric" value="{{ $value('national_id') }}">
        </div>
        <div class="form-field {{ $errors->has('nida_number') ? 'field-error' : '' }}">
            <label class="form-label" for="nida_number">{{ __('patients.nida_number') }}</label>
            <input class="input" id="nida_number" name="nida_number" type="text" value="{{ $value('nida_number') }}">
        </div>
    </div>
</div>

<div class="section">
    <h3 class="section-title">{{ __('patients.contact') }}</h3>
    <div class="field-grid">
        <div class="form-field {{ $errors->has('phone') ? 'field-error' : '' }}">
            <label class="form-label" for="phone">{{ __('patients.phone') }}</label>
            <input class="input" id="phone" name="phone" type="tel" value="{{ $value('phone') }}">
        </div>
        <div class="form-field {{ $errors->has('alt_phone') ? 'field-error' : '' }}">
            <label class="form-label" for="alt_phone">{{ __('patients.alt_phone') }}</label>
            <input class="input" id="alt_phone" name="alt_phone" type="tel" value="{{ $value('alt_phone') }}">
        </div>
        <div class="form-field {{ $errors->has('email') ? 'field-error' : '' }}">
            <label class="form-label" for="email">{{ __('patients.email') }}</label>
            <input class="input" id="email" name="email" type="email" value="{{ $value('email') }}">
        </div>
    </div>
</div>

<div class="section">
    <h3 class="section-title">{{ __('patients.region') }}</h3>
    <div class="field-grid">
        <div class="form-field {{ $errors->has('region') ? 'field-error' : '' }}">
            <label class="form-label" for="region">{{ __('patients.region') }}</label>
            <input class="input" id="region" name="region" type="text" value="{{ $value('region') }}">
        </div>
        <div class="form-field {{ $errors->has('district') ? 'field-error' : '' }}">
            <label class="form-label" for="district">{{ __('patients.district') }}</label>
            <input class="input" id="district" name="district" type="text" value="{{ $value('district') }}">
        </div>
        <div class="form-field {{ $errors->has('ward') ? 'field-error' : '' }}">
            <label class="form-label" for="ward">{{ __('patients.ward') }}</label>
            <input class="input" id="ward" name="ward" type="text" value="{{ $value('ward') }}">
        </div>
        <div class="form-field {{ $errors->has('street') ? 'field-error' : '' }}">
            <label class="form-label" for="street">{{ __('patients.street') }}</label>
            <input class="input" id="street" name="street" type="text" value="{{ $value('street') }}">
        </div>
        <div class="form-field {{ $errors->has('marital_status') ? 'field-error' : '' }}">
            <label class="form-label" for="marital_status">{{ __('patients.marital_status') }}</label>
            <input class="input" id="marital_status" name="marital_status" type="text" value="{{ $value('marital_status') }}">
        </div>
        <div class="form-field {{ $errors->has('occupation') ? 'field-error' : '' }}">
            <label class="form-label" for="occupation">{{ __('patients.occupation') }}</label>
            <input class="input" id="occupation" name="occupation" type="text" value="{{ $value('occupation') }}">
        </div>
        <div class="form-field {{ $errors->has('religion') ? 'field-error' : '' }}">
            <label class="form-label" for="religion">{{ __('patients.religion') }}</label>
            <input class="input" id="religion" name="religion" type="text" value="{{ $value('religion') }}">
        </div>
        <div class="form-field {{ $errors->has('nationality') ? 'field-error' : '' }}">
            <label class="form-label" for="nationality">{{ __('patients.nationality') }}</label>
            <input class="input" id="nationality" name="nationality" type="text" value="{{ $value('nationality') }}">
        </div>
    </div>
</div>

<div class="section">
    <h3 class="section-title">{{ __('patients.payer_type') }}</h3>
    <div class="field-grid">
        <div class="form-field {{ $errors->has('payer_type') ? 'field-error' : '' }}">
            <label class="form-label" for="payer_type">{{ __('patients.payer_type') }} <span class="form-required">*</span></label>
            <select class="select" id="payer_type" name="payer_type" required>
                @foreach (\App\Enums\PayerType::cases() as $payer)
                    <option value="{{ $payer->value }}" @selected($value('payer_type') === $payer->value)>{{ __($payer->labelKey()) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-field {{ $errors->has('nhif_number') ? 'field-error' : '' }}">
            <label class="form-label" for="nhif_number">{{ __('patients.nhif_number') }}</label>
            <input class="input" id="nhif_number" name="nhif_number" type="text" value="{{ $value('nhif_number') }}">
        </div>
    </div>
</div>

<div class="section">
    <h3 class="section-title">{{ __('patients.blood_group') }}</h3>
    <div class="field-grid">
        <div class="form-field {{ $errors->has('blood_group') ? 'field-error' : '' }}">
            <label class="form-label" for="blood_group">{{ __('patients.blood_group') }}</label>
            <select class="select" id="blood_group" name="blood_group">
                <option value=""></option>
                @foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'unknown'] as $group)
                    <option value="{{ $group }}" @selected($value('blood_group') === $group)>{{ $group === 'unknown' ? __('ui.unknown') : $group }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-field {{ $errors->has('patient_category') ? 'field-error' : '' }}">
            <label class="form-label" for="patient_category">{{ __('patients.patient_category') }}</label>
            <input class="input" id="patient_category" name="patient_category" type="text" value="{{ $value('patient_category') }}">
        </div>
    </div>
</div>

<div class="section">
    <h3 class="section-title">{{ __('patients.consents') }}</h3>
    @foreach ($consentKeys as $consentKey => $label)
        <div class="form-field checkbox-row">
            <input type="hidden" name="consents[{{ $consentKey }}]" value="0">
            <input type="checkbox" id="consent_{{ $consentKey }}" name="consents[{{ $consentKey }}]" value="1"
                   @checked((bool) old('consents.'.$consentKey, $patient ? $patient->hasActiveConsent(\App\Enums\ConsentPurpose::from($consentKey)) : false))>
            <label for="consent_{{ $consentKey }}">{{ $label }}</label>
        </div>
    @endforeach
    <div class="form-field {{ $errors->has('consent_method') ? 'field-error' : '' }}">
        <label class="form-label" for="consent_method">{{ __('patients.consent_method') }}</label>
        <select class="select" id="consent_method" name="consent_method">
            <option value=""></option>
            @foreach (['verbal', 'written', 'digital'] as $method)
                <option value="{{ $method }}" @selected($value('consent_method') === $method)>{{ __('patients.consent_method_'.$method) }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="section">
    <h3 class="section-title">{{ __('patients.next_of_kin') }}</h3>
    <p class="form-hint">{{ __('patients.next_of_kin_hint') }}</p>
    <div data-row-scaffold="#nok-rows" data-add-row="#nok-add" data-row-template="nok-template">
        <div id="nok-rows" class="stack">
            @if ($patient && $patient->nextOfKin->isNotEmpty())
                @foreach ($patient->nextOfKin as $kin)
                    @include('patients._nok-row', ['rowIndex' => $loop->index, 'kin' => $kin])
                @endforeach
            @elseif (old('next_of_kin'))
                @foreach (old('next_of_kin') as $index => $row)
                    @include('patients._nok-row', ['rowIndex' => $index, 'kin' => null])
                @endforeach
            @endif
        </div>
        <div class="row">
            <button type="button" class="btn btn-secondary btn-sm" id="nok-add">{{ __('ui.add_item') }}</button>
        </div>
    </div>
    <template id="nok-template">
        @include('patients._nok-row', ['rowIndex' => '__INDEX__', 'kin' => null])
    </template>
</div>

<div class="section">
    <h3 class="section-title">{{ __('patients.allergies') }}</h3>
    <div class="form-field checkbox-row">
        <input type="checkbox" id="no_known_allergies_confirmed" name="no_known_allergies_confirmed" value="1"
               @checked($value('no_known_allergies_confirmed'))>
        <label for="no_known_allergies_confirmed">{{ __('patients.no_known_allergies_confirm') }}</label>
    </div>
</div>

<div class="section">
    <h3 class="section-title">{{ __('patients.notes') }}</h3>
    <div class="form-field {{ $errors->has('notes') ? 'field-error' : '' }}">
        <textarea class="textarea" id="notes" name="notes" rows="3">{{ $value('notes') }}</textarea>
    </div>
</div>