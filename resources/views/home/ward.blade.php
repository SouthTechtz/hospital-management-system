@extends('layouts.app')

@section('title', __('home.my_patients'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('home.my_patients') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-wrap">
            @if ($assigned->isEmpty())
                <p class="muted">{{ __('home.no_assignments') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('triage.patient') }}</th>
                            <th>{{ __('patients.mrn') }}</th>
                            <th>{{ __('patients.registered_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assigned as $assignment)
                            <tr>
                                <td>{{ $assignment->patient->full_name }}</td>
                                <td class="mono small">{{ $assignment->patient->mrn }}</td>
                                <td class="small">{{ $assignment->assigned_at?->format(config('hms.clinical.dt_formats.date')) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection