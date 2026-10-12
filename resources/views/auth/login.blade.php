@extends('layouts.auth')

@section('title', __('auth.login_title'))

@section('content')
    <form method="POST" action="{{ route('login.attempt') }}" class="stack">
        @csrf

        <div class="form-field {{ $errors->has('username') ? 'field-error' : '' }}">
            <label class="form-label" for="username">{{ __('auth.username') }}</label>
            <input class="input" id="username" name="username" type="text"
                   value="{{ old('username') }}" autocomplete="username"
                   required autofocus>
            @error('username')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-field {{ $errors->has('password') ? 'field-error' : '' }}">
            <label class="form-label" for="password">{{ __('auth.password') }}</label>
            <input class="input" id="password" name="password" type="password"
                   autocomplete="current-password" required>
            @error('password')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">{{ __('auth.sign_in') }}</button>
        </div>
    </form>
@endsection