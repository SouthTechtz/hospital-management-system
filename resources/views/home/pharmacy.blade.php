@extends('layouts.app')

@section('title', __('home.prescriptions'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('home.prescriptions') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-wrap">
            @if ($prescriptions->isEmpty())
                <p class="muted">{{ __('home.no_pending_orders') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('triage.patient') }}</th>
                            <th>{{ __('patients.mrn') }}</th>
                            <th>{{ __('consultations.active_orders') }}</th>
                            <th>{{ __('consultations.order_priority') }}</th>
                            <th>{{ __('ui.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prescriptions as $order)
                            <tr>
                                <td>{{ $order->patient->full_name }}</td>
                                <td class="mono small">{{ $order->patient->mrn }}</td>
                                <td>
                                    @forelse ($order->items as $item)
                                        <div class="small">
                                            {{ $item->drug ? $item->drug->displayName() : ($item->description ?: '-') }}
                                            @if ($item->dose)
                                                <span class="muted"> · {{ $item->dose }} {{ $item->dose_unit }}</span>
                                            @endif
                                            @if ($item->frequency)
                                                <span class="muted"> · {{ $item->frequency }}</span>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="muted">-</span>
                                    @endforelse
                                </td>
                                <td>
                                    <span class="tag {{ $order->priority->value === 'stat' ? 'tag-critical' : ($order->priority->value === 'urgent' ? 'tag-warning' : 'tag-neutral') }}">
                                        {{ __($order->priority->labelKey()) }}
                                    </span>
                                </td>
                                <td class="small">{{ $order->ordered_at?->format(config('hms.clinical.dt_formats.datetime')) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection