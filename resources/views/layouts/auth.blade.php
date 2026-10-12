@php
    $locale = app()->getLocale();
    $title = trim((string) $__env->yieldContent('title'));
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' - '.__('ui.app_name') : __('ui.app_name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" nonce="{{ $cspNonce }}" defer></script>
</head>
<body>
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="brand-name">{{ __('ui.app_name') }}</div>
            <div class="brand-sub">{{ __('ui.app_sub') }}</div>
        </div>

        @if ($title)
            <h1 class="auth-page-title">{{ $title }}</h1>
        @endif

        @include('partials.status-messages')

        @yield('content')

        <div class="auth-extra">
            <a href="{{ route('locale.switch', $locale === 'en' ? 'sw' : 'en') }}">{{ $locale === 'en' ? 'Kiswahili' : 'English' }}</a>
        </div>
    </div>
</div>
</body>
</html>