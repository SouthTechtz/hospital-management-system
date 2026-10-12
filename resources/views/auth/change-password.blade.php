@extends('layouts.auth')

@section('title', __('auth.change_password_title'))

@section('content')
    <div class="alert alert-warning" role="status">{{ __('auth.force_password_hint') }}</div>

    <form method="POST" action="{{ route('password.change.attempt') }}" class="stack">
        @csrf

        <div class="form-field {{ $errors->has('current_password') ? 'field-error' : '' }}">
            <label class="form-label" for="current_password">{{ __('auth.current_password') }}</label>
            <input class="input" id="current_password" name="current_password" type="password"
                   autocomplete="current-password" required>
            @error('current_password')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-field {{ $errors->has('password') ? 'field-error' : '' }}">
            <label class="form-label" for="password">{{ __('auth.new_password') }}</label>
            <input class="input" id="password" name="password" type="password"
                   autocomplete="new-password" required>
            <div class="form-hint">{{ __('auth.password_rules') }}</div>
            @error('password')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-field {{ $errors->has('password_confirmation') ? 'field-error' : '' }}">
            <label class="form-label" for="password_confirmation">{{ __('auth.confirm_password') }}</label>
            <input class="input" id="password_confirmation" name="password_confirmation" type="password"
                   autocomplete="new-password" required>
            @error('password_confirmation')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">{{ __('auth.update_password') }}</button>
        </div>
    </form>
@endsection