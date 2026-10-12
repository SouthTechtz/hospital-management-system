@extends('layouts.app')

@section('title', __('admin.users'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.users') }}</h1>
        </div>
        <div class="page-actions">
            <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">{{ __('ui.back') }}</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="detail-list">
                <div class="detail-row">
                    <dt>{{ __('admin.name') }}</dt>
                    <dd>{{ $user->full_name }}</dd>
                </div>
                <div class="detail-row">
                    <dt>{{ __('admin.username') }}</dt>
                    <dd>{{ $user->username }}</dd>
                </div>
                <div class="detail-row">
                    <dt>{{ __('ui.facility') }}</dt>
                    <dd>{{ $user->facility?->short_name ?: '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
@endsection