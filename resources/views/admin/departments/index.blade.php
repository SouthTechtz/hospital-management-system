@extends('layouts.app')

@section('title', __('nav.departments'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('nav.departments') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-wrap">
            @php $departments = \App\Models\Department::with('facility')->orderBy('name')->paginate(25); @endphp
            @if ($departments->isEmpty())
                <p class="muted">{{ __('admin.no_departments') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('admin.department_name') }}</th>
                            <th>{{ __('admin.facility') }}</th>
                            <th>{{ __('admin.location') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departments as $department)
                            <tr>
                                <td>{{ $department->name }}</td>
                                <td>{{ $department->facility?->short_name ?: '-' }}</td>
                                <td>{{ $department->location ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $departments->links() }}
            @endif
        </div>
    </div>
@endsection