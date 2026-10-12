@extends('layouts.app')

@section('title', __('admin.roles_title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.roles_title') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-wrap">
            @php $roles = \App\Models\Role::withCount('users')->orderBy('name')->get(); @endphp
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('admin.roles') }}</th>
                        <th>{{ __('admin.users_with_role') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td>{{ $role->display_name }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td class="num">
                                <a class="btn btn-sm btn-secondary" href="{{ route('admin.roles.show', $role) }}">{{ __('ui.view') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection