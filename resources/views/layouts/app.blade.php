@php
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
    $canSearchPatients = $user && $user->hasAnyPermission(['patient.search', 'patient.view.any']);
    $canViewAdmin = $user && $user->hasPermission('user.view');
    $canViewRoles = $user && $user->hasPermission('role.view');
    $canViewAudit = $user && $user->hasPermission('audit.view');
    $canReviewBreakGlass = $user && $user->hasPermission('breakglass.review');
    $canManageFacilities = $user && $user->hasPermission('facility.manage');
    $canManageDepartments = $user && $user->hasPermission('department.manage');
    $canManageCredentials = $user && ($user->hasPermission('credentials.manage') || $user->hasPermission('apikey.manage'));
    $isPharmacist = $user && $user->hasRole('pharmacist');
    $isLaboratory = $user && $user->hasRole('laboratory_technologist');
    $isRadiologist = $user && $user->hasRole('radiologist');
    $isWardNurse = $user && $user->hasRole('ward_nurse');
    $isCashier = $user && $user->hasRole('cashier');
    $isRecords = $user && $user->hasRole('records_officer');
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
<div class="app-shell">
    <aside class="sidebar" data-sidebar>
        <div class="sidebar-brand">
            <div class="brand-name">{{ __('ui.app_name') }}</div>
            <div class="brand-sub">{{ __('ui.app_sub') }}</div>
        </div>
        <nav class="sidebar-nav" aria-label="{{ __('nav.main_menu') }}">
            <div class="nav-group">
                <div class="nav-group-title">{{ __('nav.group_my_work') }}</div>
                <a class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">{{ __('nav.home') }}</a>
                @if ($canSearchPatients)
                    <a class="nav-item {{ request()->routeIs('patients.*') ? 'active' : '' }}" href="{{ route('patients.index') }}">{{ __('nav.patients') }}</a>
                @endif
                <a class="nav-item {{ request()->routeIs('triage.*') ? 'active' : '' }}" href="{{ route('triage.index') }}">{{ __('nav.triage') }}</a>
            </div>

            <div class="nav-group">
                <div class="nav-group-title">{{ __('nav.group_diagnostics') }}</div>
                @if ($isPharmacist)
                    <a class="nav-item" href="{{ route('home') }}">{{ __('nav.pharmacy') }}</a>
                @endif
                @if ($isLaboratory)
                    <a class="nav-item" href="{{ route('home') }}">{{ __('nav.laboratory') }}</a>
                @endif
                @if ($isRadiologist)
                    <a class="nav-item" href="{{ route('home') }}">{{ __('nav.radiology') }}</a>
                @endif
            </div>

            <div class="nav-group">
                <div class="nav-group-title">{{ __('nav.group_nursing') }}</div>
                @if ($isWardNurse)
                    <a class="nav-item" href="{{ route('home') }}">{{ __('nav.ward') }}</a>
                @endif
            </div>

            <div class="nav-group">
                <div class="nav-group-title">{{ __('nav.group_administration') }}</div>
                @if ($isCashier)
                    <a class="nav-item" href="{{ route('home') }}">{{ __('nav.cashier') }}</a>
                @endif
                @if ($isRecords)
                    <a class="nav-item" href="{{ route('home') }}">{{ __('nav.records') }}</a>
                @endif
            </div>

            @if ($canViewAdmin || $canViewRoles || $canViewAudit || $canReviewBreakGlass || $canManageFacilities || $canManageDepartments || $canManageCredentials)
            <div class="nav-group">
                <div class="nav-group-title">{{ __('nav.group_system') }}</div>
                @if ($canViewAdmin)
                    <a class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">{{ __('nav.admin_dashboard') }}</a>
                    <a class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">{{ __('nav.users') }}</a>
                @endif
                @if ($canViewRoles)
                    <a class="nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}" href="{{ route('admin.roles.index') }}">{{ __('nav.roles') }}</a>
                @endif
                @if ($canViewAudit)
                    <a class="nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}" href="{{ route('admin.audit.index') }}">{{ __('nav.audit') }}</a>
                @endif
                @if ($canReviewBreakGlass)
                    <a class="nav-item {{ request()->routeIs('admin.break-glass.*') ? 'active' : '' }}" href="{{ route('admin.break-glass.index') }}">{{ __('nav.break_glass') }}</a>
                @endif
                @if ($canManageFacilities)
                    <a class="nav-item {{ request()->routeIs('admin.facilities.*') ? 'active' : '' }}" href="{{ route('admin.facilities.index') }}">{{ __('nav.facilities') }}</a>
                @endif
                @if ($canManageDepartments)
                    <a class="nav-item {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}" href="{{ route('admin.departments.index') }}">{{ __('nav.departments') }}</a>
                @endif
                @if ($canManageCredentials)
                    <a class="nav-item {{ request()->routeIs('admin.credentials.*') ? 'active' : '' }}" href="{{ route('admin.credentials.index') }}">{{ __('nav.credentials') }}</a>
                @endif
            </div>
            @endif
        </nav>
    </aside>

    <div class="main">
        <header class="topbar">
            <button type="button" class="sidebar-toggle" data-sidebar-toggle>{{ __('ui.menu') }}</button>
            <div class="topbar-title">{{ $title ?: __('ui.app_name') }}</div>

            @if ($canSearchPatients)
            <div class="topbar-search">
                <form method="GET" action="{{ route('patients.index') }}" role="search">
                    <input class="input" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('nav.search_patients') }}" aria-label="{{ __('nav.search_patients') }}">
                    <button class="btn btn-primary btn-sm" type="submit">{{ __('ui.search') }}</button>
                </form>
            </div>
            @endif

            <div class="topbar-actions">
                <a class="btn btn-sm btn-secondary" href="{{ route('locale.switch', $locale === 'en' ? 'sw' : 'en') }}">{{ $locale === 'en' ? 'Kiswahili' : 'English' }}</a>
                <span class="small muted">{{ $user?->full_name ?: '' }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-secondary" type="submit">{{ __('nav.logout') }}</button>
                </form>
            </div>
        </header>

        <main class="content">
            @include('partials.status-messages')

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>