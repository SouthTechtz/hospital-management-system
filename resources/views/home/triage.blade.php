@extends('layouts.app')

@section('title', __('triage.waitlist'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('triage.waitlist') }}</h1>
            <p class="muted small">{{ __('home.triage_priorities') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-wrap">
            @if ($waiting->isEmpty())
                <p class="muted">{{ __('home.no_waiting') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('triage.patient') }}</th>
                            <th>{{ __('patients.mrn') }}</th>
                            <th>{{ __('triage.category') }}</th>
                            <th>{{ __('triage.priority') }}</th>
                            <th>{{ __('triage.added_at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($waiting as $entry)
                            <tr>
                                <td>{{ $entry->patient->full_name }}</td>
                                <td class="mono small">{{ $entry->patient->mrn }}</td>
                                <td>
                                    @if ($entry->triage_category)
                                        <span class="tag triage-{{ $entry->triage_category->value }}">{{ __($entry->triage_category->labelKey()) }}</span>
                                    @else
                                        <span class="tag tag-neutral">{{ __('ui.unknown') }}</span>
                                    @endif
                                </td>
                                <td class="small">{{ $entry->priority }}</td>
                                <td class="small">{{ $entry->added_at?->format(config('hms.clinical.dt_formats.time')) }}</td>
                                <td class="num">
                                    @if ($entry->encounter_id)
                                        <a class="btn btn-sm btn-secondary" href="{{ route('triage.form', $entry->encounter_id) }}">{{ __('triage.assess') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection