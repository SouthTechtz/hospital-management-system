@extends('layouts.app')

@section('title', __('home.title'))

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ __('home.title') }}</h1>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @php
                $moduleKey = match ($module) {
                    'laboratory' => 'nav.laboratory',
                    'radiology' => 'nav.radiology',
                    default => 'ui.module',
                };
            @endphp
            <h2>{{ __('home.module_placeholder', ['module' => __($moduleKey)]) }}</h2>
            <p class="muted">{{ __('home.module_available') }}</p>
        </div>
    </div>
@endsection