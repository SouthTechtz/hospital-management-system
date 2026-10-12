@extends('layouts.app')

@section('title', __('nav.facilities'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('nav.facilities') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-wrap">
            @php $facilities = \App\Models\Facility::orderBy('name')->paginate(25); @endphp
            @if ($facilities->isEmpty())
                <p class="muted">{{ __('admin.no_facilities') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('admin.facility_name') }}</th>
                            <th>{{ __('admin.short_name') }}</th>
                            <th>{{ __('admin.hfr_code') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($facilities as $facility)
                            <tr>
                                <td>{{ $facility->name }}</td>
                                <td>{{ $facility->short_name }}</td>
                                <td class="mono small">{{ $facility->hfr_code }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $facilities->links() }}
            @endif
        </div>
    </div>
@endsection