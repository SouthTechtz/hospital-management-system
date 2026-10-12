@extends('layouts.app')

@section('title', __('triage.waitlist'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('triage.waitlist') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-wrap">
            @if ($waiting->isEmpty())
                <p class="muted">{{ __('triage.no_waiting') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('triage.patient') }}</th>
                            <th>{{ __('triage.category') }}</th>
                            <th>{{ __('triage.priority') }}</th>
                            <th>{{ __('triage.queue') }}</th>
                            <th>{{ __('triage.added_at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($waiting as $entry)
                            <tr>
                                <td>
                                    {{ $entry->patient?->full_name ?: '-' }}<br>
                                    <span class="mono small">{{ $entry->patient?->mrn ?: '' }}</span>
                                </td>
                                <td>
                                    @if ($entry->triage_category)
                                        <span class="tag triage-{{ $entry->triage_category->value }}">{{ __($entry->triage_category->labelKey()) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $entry->priority }}</td>
                                <td>{{ $entry->queue?->name ?: '-' }}</td>
                                <td class="small">{{ $entry->added_at?->format(config('hms.clinical.dt_formats.datetime')) ?: '-' }}</td>
                                <td class="num">
                                    @if ($entry->encounter)
                                        <a class="btn btn-sm btn-secondary" href="{{ route('encounters.show', $entry->encounter) }}">{{ __('ui.view') }}</a>
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