@extends('layouts.auth')

@section('title', __('mfa.challenge_title'))

@section('content')
    <div class="alert alert-info" role="status">{{ __('mfa.challenge_intro') }}</div>

    <form method="POST" action="{{ route('mfa.challenge.verify') }}" class="stack">
        @csrf

        <div class="form-field {{ $errors->has('code') ? 'field-error' : '' }}">
            <label class="form-label" for="code">{{ __('mfa.code_label') }}</label>
            <input class="input mono" id="code" name="code" type="text" inputmode="numeric"
                   pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code"
                   required autofocus>
            @error('code')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">{{ __('mfa.verify') }}</button>
        </div>
    </form>
@endsection