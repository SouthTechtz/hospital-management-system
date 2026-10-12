@extends('layouts.app')

@section('title', __('admin.create_user'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.create_user') }}</h1>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}" class="stack">
                @csrf
                <div class="field-grid">
                    <div class="form-field">
                        <label class="form-label" for="first_name">{{ __('patients.first_name') }}</label>
                        <input class="input" id="first_name" name="first_name" type="text" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="last_name">{{ __('patients.last_name') }}</label>
                        <input class="input" id="last_name" name="last_name" type="text" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="username">{{ __('admin.username') }}</label>
                        <input class="input" id="username" name="username" type="text" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="facility_id">{{ __('ui.facility') }}</label>
                        <select class="select" id="facility_id" name="facility_id" required>
                            @foreach (\App\Models\Facility::all() as $facility)
                                <option value="{{ $facility->getKey() }}">{{ $facility->short_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="password">{{ __('admin.password') }}</label>
                        <input class="input" id="password" name="password" type="password" required>
                    </div>
                    <div class="form-field">
                        <label class="form-label" for="password_confirmation">{{ __('admin.password_confirmation') }}</label>
                        <input class="input" id="password_confirmation" name="password_confirmation" type="password" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">{{ __('admin.create_user') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection