@extends('layouts.app')

@section('title', __('patients.register_title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('patients.register_title') }}</h1>
            <p class="muted small">{{ __('patients.register') }}</p>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('patients.index') }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    @if ($duplicates = session('duplicates'))
        <div class="alert alert-warning" role="status">
            <h2 class="alert-title">{{ __('patients.duplicate_warning') }}</h2>
            <p>{{ __('patients.duplicate_hint') }}</p>
            <ul class="stack">
                @foreach ($duplicates as $candidate)
                    <li>
                        <a href="{{ route('patients.show', $candidate) }}">{{ $candidate->full_name }} ({{ $candidate->mrn }})</a>
                        <span class="muted small"> - {{ $candidate->ageDisplay() }} {{ __('patients.sex') }}:
                            {{ $candidate->sex ? __($candidate->sex->labelKey()) : '-' }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="small">{{ __('ui.required_hint') }}</p>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('patients.store') }}" data-duplicate-form>
                @csrf

                @include('patients._form-fields', ['patient' => null])

                @if ($duplicates = session('duplicates'))
                    <div class="form-field checkbox-row">
                        <input type="checkbox" id="confirm_duplicate" name="confirm_duplicate" value="1">
                        <label for="confirm_duplicate">{{ __('patients.confirm_duplicate') }}</label>
                    </div>
                @endif

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">{{ __('patients.register') }}</button>
                    <a class="btn btn-ghost" href="{{ route('patients.index') }}">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection