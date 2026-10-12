@extends('layouts.app')

@section('title', __('admin.roles_title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.roles_title') }} - {{ $role->display_name }}</h1>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('admin.roles.index') }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.roles.permissions.update', $role) }}" class="stack">
                @csrf
                @method('PUT')
                <p class="muted">{{ __('admin.permission_matrix') }}</p>
                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">{{ __('admin.save_permissions') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection