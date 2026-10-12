@extends('layouts.app')

@section('title', __('home.recent_registrations'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('home.recent_registrations') }}</h1>
        </div>
        <div class="page-actions">
            @can('create', App\Models\Patient::class)
                <a class="btn btn-primary" href="{{ route('patients.create') }}">{{ __('patients.register') }}</a>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-body table-wrap">
            @if ($recentRegistrations->isEmpty())
                <p class="muted">{{ __('home.no_registrations') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('patients.full_name') }}</th>
                            <th>{{ __('patients.mrn') }}</th>
                            <th>{{ __('patients.sex') }}</th>
                            <th>{{ __('patients.registered_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentRegistrations as $patient)
                            <tr>
                                <td>{{ $patient->full_name }}</td>
                                <td class="mono small">{{ $patient->mrn }}</td>
                                <td>{{ $patient->sex ? __($patient->sex->labelKey()) : '-' }}</td>
                                <td class="small">{{ $patient->registered_at?->format(config('hms.clinical.dt_formats.datetime')) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection