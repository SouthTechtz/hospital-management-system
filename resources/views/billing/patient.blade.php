@extends('layouts.app')

@section('title', __('billing.title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('billing.title') }}</h1>
            <p class="muted small">{{ $patient->full_name }} - <span class="mono">{{ $patient->mrn }}</span></p>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('patients.show', $patient) }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('billing.invoices') }}</h2>
        </div>
        <div class="card-body table-wrap">
            @if ($invoices->isEmpty())
                <p class="muted">{{ __('billing.no_invoices') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('billing.invoice_number') }}</th>
                            <th>{{ __('billing.status') }}</th>
                            <th>{{ __('billing.total') }}</th>
                            <th>{{ __('billing.balance') }}</th>
                            <th>{{ __('ui.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoices as $invoice)
                            <tr>
                                <td class="mono small">{{ $invoice->invoice_number }}</td>
                                <td>{{ __($invoice->status->labelKey()) }}</td>
                                <td class="num">{{ number_format($invoice->total_amount, 2) }}</td>
                                <td class="num">{{ number_format($invoice->balance_amount, 2) }}</td>
                                <td class="small">{{ $invoice->created_at?->format(config('hms.clinical.dt_formats.date')) ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection