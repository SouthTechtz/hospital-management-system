@extends('layouts.app')

@section('title', __('nav.credentials'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('nav.credentials') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="muted">{{ __('admin.no_credentials') }}</p>
        </div>
    </div>
@endsection