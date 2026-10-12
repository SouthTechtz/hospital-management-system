@extends('layouts.app')

@section('title', __('patients.merge_into'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('patients.merge_into') }}</h1>
            <p class="muted small">{{ $patient->full_name }} - <span class="mono">{{ $patient->mrn }}</span></p>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('patients.show', $patient) }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    @if ($patient->merge_status === 'merged')
        <div class="alert alert-danger" role="status">
            {{ __('patients.status_merged') }}
        </div>
    @endif

    @if ($patient->merge_status !== 'merged')
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('patients.merge_into') }}</h2>
        </div>
        <div class="card-body">
            <div class="alert alert-warning" role="status">{{ __('patients.merge_warning') }}</div>

            <form method="GET" action="{{ route('patients.merge.index', $patient) }}" class="filter-bar grow" role="search">
                <input class="input" type="search" name="q" value="{{ $q }}" placeholder="{{ __('patients.merge_target') }}" aria-label="{{ __('patients.merge_target') }}">
                <button class="btn btn-primary" type="submit">{{ __('ui.search') }}</button>
                @if ($q !== '')
                    <a class="btn btn-ghost" href="{{ route('patients.merge.index', $patient) }}">{{ __('admin.clear_filters') }}</a>
                @endif
            </form>

            @if ($q !== '')
                @if ($candidates->isEmpty())
                    <p class="muted">{{ __('ui.no_results') }}</p>
                @else
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('patients.mrn') }}</th>
                                    <th>{{ __('patients.full_name') }}</th>
                                    <th>{{ __('patients.sex') }}</th>
                                    <th>{{ __('patients.age') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($candidates as $candidate)
                                    <tr>
                                        <td class="mono small">{{ $candidate->mrn }}</td>
                                        <td>{{ $candidate->full_name }}</td>
                                        <td>{{ $candidate->sex ? __($candidate->sex->labelKey()) : '-' }}</td>
                                        <td class="small">{{ $candidate->ageDisplay() }}</td>
                                        <td class="num">
                                            <form method="POST" action="{{ route('patients.merge.store', $patient) }}">
                                                @csrf
                                                <input type="hidden" name="target_patient_id" value="{{ $candidate->getKey() }}">
                                                <div class="row-items">
                                                    <input class="input" name="reason" type="text" required
                                                           placeholder="{{ __('patients.merge_reason') }}"
                                                           aria-label="{{ __('patients.merge_reason') }}">
                                                    <button class="btn btn-danger btn-sm" type="submit">{{ __('patients.merge_into') }}</button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('patients.merge_history') }}</h2>
        </div>
        <div class="card-body table-wrap">
            @if ($mergeHistory->isEmpty())
                <p class="muted">{{ __('patients.no_merge_history') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('patients.mrn') }}</th>
                            <th>{{ __('patients.full_name') }}</th>
                            <th>{{ __('patients.merge_reason') }}</th>
                            <th>{{ __('admin.occurred_at') }}</th>
                            <th>{{ __('ui.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mergeHistory as $merge)
                            <tr>
                                <td class="mono small">{{ $merge->target?->mrn ?: '-' }}</td>
                                <td>{{ $merge->target?->full_name ?: '-' }}</td>
                                <td class="small">{{ $merge->reason }}</td>
                                <td class="small">{{ $merge->merged_at?->format(config('hms.clinical.dt_formats.datetime')) ?: '-' }}</td>
                                <td>{{ $merge->isReversed() ? __('break_glass.status') : __('ui.confirmed') }}</td>
                                <td class="num">
                                    @if (! $merge->isReversed())
                                        <form method="POST" action="{{ route('patients.merge.unmerge', [$patient, $merge]) }}">
                                            @csrf
                                            <input class="input" name="reason" type="text" required
                                                   placeholder="{{ __('patients.unmerge_reason') }}"
                                                   aria-label="{{ __('patients.unmerge_reason') }}">
                                            <button class="btn btn-secondary btn-sm" type="submit">{{ __('patients.unmerge') }}</button>
                                        </form>
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