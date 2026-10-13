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
        <div class="card-header">
            <form method="GET" action="{{ route('admin.users.index') }}" class="toolbar" style="gap: .5rem">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('ui.search') }}" class="form-control" style="max-width: 200px" aria-label="{{ __('ui.search') }}">
                <button type="submit" class="btn btn-sm btn-secondary">{{ __('ui.search') }}</button>
                @if(request('q'))
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-link">{{ __('ui.clear') }}</a>
                @endif
            </form>
        </div>
        <div class="card-body table-wrap">
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