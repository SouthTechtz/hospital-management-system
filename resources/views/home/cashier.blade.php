@extends('layouts.app')

@section('title', __('home.open_invoices_section'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('home.open_invoices_section') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-wrap">
            @if ($openInvoices->isEmpty())
                <p class="muted">{{ __('home.no_open_invoices') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('billing.invoice_number') }}</th>
                            <th>{{ __('triage.patient') }}</th>
                            <th>{{ __('billing.status') }}</th>
                            <th class="num">{{ __('billing.total') }}</th>
                            <th class="num">{{ __('billing.balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($openInvoices as $invoice)
                            <tr>
                                <td class="mono small">{{ $invoice->invoice_number }}</td>
                                <td>
                                    {{ $invoice->patient->full_name }}
                                    <div class="small muted mono">{{ $invoice->patient->mrn }}</div>
                                </td>
                                <td>
                                    <span class="tag {{ $invoice->status->value === 'open' ? 'tag-warning' : 'tag-info' }}">
                                        {{ __($invoice->status->labelKey()) }}
                                    </span>
                                </td>
                                <td class="num">{{ number_format((float) $invoice->total, 0) }} TZS</td>
                                <td class="num">{{ number_format((float) $invoice->balance, 0) }} TZS</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection