@extends('layouts.app')

@section('title', __('admin.edit_user'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.edit_user') }}</h1>
            <p class="muted small">{{ $user->full_name }}</p>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="stack">
                @csrf
                @method('PUT')
                <div class="field-grid">
                    <div class="form-field">
                        <label class="form-label" for="first_name">{{ __('patients.first_name') }}</label>
                        <input class="input" id="first_name" name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="last_name">{{ __('patients.last_name') }}</label>
                        <input class="input" id="last_name" name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">{{ __('ui.save') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection