@extends('layouts.auth')

@section('title', __('mfa.setup_title'))

@section('content')
    <div class="alert alert-info" role="status">{{ __('mfa.setup_intro') }}</div>

    <div class="qr-box">
        <img src="{{ $qrDataUri }}" alt="{{ __('mfa.qr_alt') }}" width="200" height="200">
    </div>

    <p class="small muted">{{ __('mfa.secret_label') }}</p>
    <div class="secret-box">{{ $readableSecret }}</div>

    <p class="small muted">{{ __('mfa.manual_uri') }}</p>
    <div class="secret-box small mono">{{ $otpauthUri }}</div>

    <form method="POST" action="{{ route('mfa.setup.confirm') }}" class="stack">
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
            <button class="btn btn-primary" type="submit">{{ __('mfa.confirm') }}</button>
        </div>
    </form>
@endsection