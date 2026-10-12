@extends('layouts.app')

@section('title', __('admin.dashboard'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('admin.dashboard') }}</h1>
        </div>
    </div>

    <div class="card-grid">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">{{ __('home.title') }}</h2>
            </div>
            <div class="card-body">
                <dl class="detail-list">
                    <div class="detail-row">
                        <dt>{{ __('home.users') }}</dt>
                        <dd>{{ \App\Models\User::count() }}</dd>
                    </div>
                    <div class="detail-row">
                        <dt>{{ __('home.patients') }}</dt>
                        <dd>{{ \App\Models\Patient::count() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection