@extends('layouts.app')

@section('title', __('patients.title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('patients.title') }}</h1>
            <p class="muted small">{{ __('patients.mpi_hint') }}</p>
        </div>
        <div class="page-actions">
            @can('create', App\Models\Patient::class)
                <a class="btn btn-primary" href="{{ route('patients.create') }}">{{ __('patients.register') }}</a>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('patients.index') }}" class="filter-bar grow" role="search">
                <input class="input" type="search" name="q" value="{{ $term }}" placeholder="{{ __('patients.search_placeholder') }}" aria-label="{{ __('patients.search_placeholder') }}">
                <button class="btn btn-primary" type="submit">{{ __('ui.search') }}</button>
                @if ($term !== '')
                    <a class="btn btn-ghost" href="{{ route('patients.index') }}">{{ __('admin.clear_filters') }}</a>
                @endif
            </form>
        </div>
        <div class="card-body table-wrap">
            @if ($patients->isEmpty())
                <p class="muted">{{ __('ui.no_records') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('patients.mrn') }}</th>
                            <th>{{ __('patients.full_name') }}</th>
                            <th>{{ __('patients.sex') }}</th>
                            <th>{{ __('patients.age') }}</th>
                            <th>{{ __('patients.registered_at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patients as $patient)
                            <tr>
                                <td class="mono small">{{ $patient->mrn }}</td>
                                <td>{{ $patient->full_name }}</td>
                                <td>{{ $patient->sex ? __($patient->sex->labelKey()) : '-' }}</td>
                                <td class="small">{{ $patient->ageDisplay() }}</td>
                                <td class="small">{{ $patient->registered_at?->format(config('hms.clinical.dt_formats.date')) }}</td>
                                <td class="num">
                                    <a class="btn btn-sm btn-secondary" href="{{ route('patients.show', $patient) }}">{{ __('ui.view') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $patients->links() }}
            @endif
        </div>
    </div>
@endsection