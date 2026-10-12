@extends('layouts.app')

@section('title', __('admin.users'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.users') }}</h1>
        </div>
        <div class="page-actions">
            <a class="btn btn-primary" href="{{ route('admin.users.create') }}">{{ __('admin.create_user') }}</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-wrap">
            @php $users = \App\Models\User::with('facility')->orderBy('last_name')->paginate(25); @endphp
            @if ($users->isEmpty())
                <p class="muted">{{ __('admin.no_users') }}</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('admin.name') }}</th>
                            <th>{{ __('admin.username') }}</th>
                            <th>{{ __('admin.facility') }}</th>
                            <th>{{ __('admin.is_active') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->full_name }}</td>
                                <td class="mono">{{ $user->username }}</td>
                                <td>{{ $user->facility?->short_name ?: '-' }}</td>
                                <td>{{ $user->is_active ? __('ui.yes') : __('ui.no') }}</td>
                                <td class="num">
                                    <a class="btn btn-sm btn-secondary" href="{{ route('admin.users.edit', $user) }}">{{ __('ui.edit') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $users->links() }}
            @endif
        </div>
    </div>
@endsection