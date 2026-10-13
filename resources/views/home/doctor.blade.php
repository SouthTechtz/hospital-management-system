@extends('layouts.app')

@section('title', __('home.title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('nav.consultation') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>{{ __('home.consultation_queue') }}</h2>
            <span class="small muted">{{ __('home.triage_priorities') }}</span>
        </div>
        <div class="card-body table-wrap">
            @if ($consultationQueue->isEmpty())
                <p class="muted">{{ __('home.no_waiting') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('triage.patient') }}</th>
                            <th>{{ __('patients.mrn') }}</th>
                            <th>{{ __('triage.category') }}</th>
                            <th>{{ __('triage.added_at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($consultationQueue as $entry)
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
                                <td class="small">{{ $entry->added_at?->format(config('hms.clinical.dt_formats.time')) }}</td>
                                <td class="num">
                                    @if ($entry->encounter_id)
                                        <a class="btn btn-sm btn-secondary" href="{{ route('consultations.show', $entry->encounter_id) }}">{{ __('consultations.title') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <div class="card-header"><h2>{{ __('home.pending_orders') }}</h2></div>
            <div class="card-body table-wrap">
                @if ($pendingOrders->isEmpty())
                    <p class="muted">{{ __('home.no_pending_orders') }}</p>
                @else
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('triage.patient') }}</th>
                                <th>{{ __('orders.type') }}</th>
                                <th>{{ __('ui.status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingOrders as $order)
                                <tr>
                                    <td>
                                        {{ $order->patient->full_name }}
                                        <div class="small muted mono">{{ $order->patient->mrn }}</div>
                                    </td>
                                    <td>{{ __($order->order_type->labelKey()) }}</td>
                                    <td><span class="tag tag-info">{{ __($order->status->labelKey()) }}</span></td>
                                    <td class="num">
                                        @can('order.view', $order)
                                            <a class="btn btn-sm btn-secondary" href="{{ route('orders.show', $order) }}">{{ __('ui.view') }}</a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2>{{ __('home.open_encounters_section') }}</h2></div>
            <div class="card-body table-wrap">
                @if ($openEncounters->isEmpty())
                    <p class="muted">{{ __('home.no_waiting') }}</p>
                @else
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('triage.patient') }}</th>
                                <th>{{ __('patients.visit_number') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($openEncounters as $encounter)
                                <tr>
                                    <td>{{ $encounter->patient->full_name }}</td>
                                    <td class="mono small">{{ $encounter->visit_number }}</td>
                                    <td class="num">
                                        <a class="btn btn-sm btn-secondary" href="{{ route('encounters.show', $encounter) }}">{{ __('ui.view') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection