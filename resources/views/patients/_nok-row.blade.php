@php
    $kin = $kin ?? null;
    $rowValue = fn (string $key, $default = null) => $kin
        ? old('next_of_kin.'.$rowIndex.'.'.$key, $kin->getAttribute($key) ?? $default)
        : old('next_of_kin.'.$rowIndex.'.'.$key, $default);
@endphp

<div class="nok-row" data-row>
    <div class="field-grid">
        <div class="form-field">
            <label class="form-label" for="nok_{{ $rowIndex }}_full_name">{{ __('patients.next_of_kin_name') }}</label>
            <input class="input" id="nok_{{ $rowIndex }}_full_name" name="next_of_kin[{{ $rowIndex }}][full_name]" type="text"
                   value="{{ $rowValue('full_name') }}" placeholder="{{ __('patients.next_of_kin_name') }}">
        </div>
        <div class="form-field">
            <label class="form-label" for="nok_{{ $rowIndex }}_relationship">{{ __('patients.next_of_kin_relationship') }}</label>
            <input class="input" id="nok_{{ $rowIndex }}_relationship" name="next_of_kin[{{ $rowIndex }}][relationship]" type="text"
                   value="{{ $rowValue('relationship') }}" placeholder="{{ __('patients.next_of_kin_relationship') }}">
        </div>
        <div class="form-field">
            <label class="form-label" for="nok_{{ $rowIndex }}_phone">{{ __('patients.next_of_kin_phone') }}</label>
            <input class="input" id="nok_{{ $rowIndex }}_phone" name="next_of_kin[{{ $rowIndex }}][phone]" type="tel"
                   value="{{ $rowValue('phone') }}">
        </div>
        <div class="form-field">
            <label class="form-label" for="nok_{{ $rowIndex }}_alt_phone">{{ __('patients.alt_phone') }}</label>
            <input class="input" id="nok_{{ $rowIndex }}_alt_phone" name="next_of_kin[{{ $rowIndex }}][alt_phone]" type="tel"
                   value="{{ $rowValue('alt_phone') }}">
        </div>
        <div class="form-field">
            <label class="form-label" for="nok_{{ $rowIndex }}_address">{{ __('patients.next_of_kin_address') }}</label>
            <input class="input" id="nok_{{ $rowIndex }}_address" name="next_of_kin[{{ $rowIndex }}][address]" type="text"
                   value="{{ $rowValue('address') }}">
        </div>
        <div class="form-field checkbox-row nok-primary">
            <input type="checkbox" id="nok_{{ $rowIndex }}_is_primary" name="next_of_kin[{{ $rowIndex }}][is_primary]" value="1"
                   @checked((bool) $rowValue('is_primary'))>
            <label for="nok_{{ $rowIndex }}_is_primary">{{ __('patients.next_of_kin_primary') }}</label>
        </div>
    </div>
    <div class="row">
        <button type="button" class="btn btn-secondary btn-sm" data-remove-row>{{ __('ui.remove') }}</button>
    </div>
</div>