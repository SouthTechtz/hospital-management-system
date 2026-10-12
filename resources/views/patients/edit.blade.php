@extends('layouts.app')

@section('title', __('patients.edit_title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('patients.edit_title') }}</h1>
            <p class="muted small">{{ $patient->full_name }} - {{ $patient->mrn }}</p>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('patients.show', $patient) }}">{{ __('ui.back') }}</a>
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
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('patients.update', $patient) }}" data-duplicate-form>
                @csrf
                @method('PUT')

                @include('patients._form-fields', ['patient' => $patient])

                @if ($duplicates = session('duplicates'))
                    <div class="form-field checkbox-row">
                        <input type="checkbox" id="confirm_duplicate" name="confirm_duplicate" value="1">
                        <label for="confirm_duplicate">{{ __('patients.confirm_duplicate') }}</label>
                    </div>
                @endif

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">{{ __('ui.save') }}</button>
                    <a class="btn btn-ghost" href="{{ route('patients.show', $patient) }}">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection