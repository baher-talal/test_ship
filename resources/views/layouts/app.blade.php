<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Dashboard')) - {{ __(config('app.name')) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-w: 260px; --brand-dark: #0a2a5e; }
        body { font-family: 'Cairo', sans-serif; background: #f4f6fb; }
        .sidebar { position: fixed; top: 0; inset-inline-start: 0; bottom: 0; width: var(--sidebar-w); background: var(--brand-dark); color: #cfd8ea; overflow-y: auto; z-index: 1040; transition: transform .25s; }
        .sidebar .brand { padding: 1.1rem 1.25rem; font-weight: 700; font-size: 1.15rem; color: #fff; border-bottom: 1px solid rgba(255,255,255,.08); }
        .sidebar .nav-link { color: #cfd8ea; padding: .65rem 1.25rem; display: flex; align-items: center; gap: .6rem; border-inline-start: 3px solid transparent; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.06); color: #fff; }
        .sidebar .nav-link.active { background: rgba(255,255,255,.1); color: #fff; border-inline-start-color: #4da3ff; }
        .sidebar .nav-section { font-size: .72rem; letter-spacing: .08em; padding: 1rem 1.25rem .3rem; color: #7f8fb0; }
        .main { margin-inline-start: var(--sidebar-w); min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid #e6e9f2; position: sticky; top: 0; z-index: 1030; }
        .card { border: 0; box-shadow: 0 1px 3px rgba(16,24,40,.08); border-radius: .75rem; }
        .card-header { background: transparent; font-weight: 600; border-bottom: 1px solid #eef0f6; }
        .stat-card .icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
        .table > :not(caption) > * > * { padding: .75rem .85rem; }
        .table thead th { font-size: .82rem; color: #667085; font-weight: 600; background: #f9fafc; white-space: nowrap; }
        .timeline { position: relative; padding-inline-start: 1.5rem; }
        .timeline::before { content: ''; position: absolute; inset-inline-start: 7px; top: 6px; bottom: 6px; width: 2px; background: #dfe3ee; }
        .timeline-item { position: relative; padding-bottom: 1.25rem; }
        .timeline-item::before { content: ''; position: absolute; inset-inline-start: -1.5rem; top: 5px; width: 16px; height: 16px; border-radius: 50%; background: #fff; border: 3px solid #adb5bd; }
        .timeline-item.first::before { background: #0d6efd; border-color: #0d6efd; }
        .overlay { display: none; }
        @media (max-width: 991px) {
            [dir=rtl] .sidebar { transform: translateX(100%); }
            [dir=ltr] .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: none; }
            .main { margin-inline-start: 0; }
            .overlay.show { display: block; position: fixed; top: 0; right: 0; bottom: 0; left: 0; background: rgba(0,0,0,.4); z-index: 1035; }
        }
        @media print { .sidebar, .topbar, .no-print { display: none !important; } .main { margin: 0; } body { background: #fff; } }
    </style>
    @stack('styles')
</head>
<body>
@php
    $authUser = auth()->user();
    $unreadCount = $authUser->userNotifications()->whereNull('read_at')->count();
    $recentNotifs = $authUser->userNotifications()->take(6)->get();
    $nav = function ($pattern) { return request()->routeIs($pattern) ? 'active' : ''; };
    $can = function ($permission) use ($authUser) { return $authUser->hasPermission($permission); };
@endphp

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <div class="brand"><i class="bi bi-box-seam-fill text-info"></i> {{ __(config('app.name')) }}</div>
    <nav class="nav flex-column pb-4">
        <a class="nav-link {{ $nav('dashboard') }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}</a>

        @if($can('shipments.view') || $can('shipments.create') || $can('customers.view') || $can('invoices.view'))
            <div class="nav-section">{{ __('Operations') }}</div>
        @endif
        @if($can('shipments.view'))
            <a class="nav-link {{ $nav('shipments.index') }}" href="{{ route('shipments.index') }}"><i class="bi bi-boxes"></i> {{ $authUser->isCourier() ? __('My shipments') : __('Shipments') }}</a>
        @endif
        @if($can('shipments.create'))
            <a class="nav-link {{ $nav('shipments.create') }}" href="{{ route('shipments.create') }}"><i class="bi bi-plus-circle"></i> {{ __('New shipment') }}</a>
        @endif
        @if($can('customers.view'))
            <a class="nav-link {{ $nav('customers.*') }}" href="{{ route('customers.index') }}"><i class="bi bi-people"></i> {{ __('Customers') }}</a>
        @endif
        @if($can('invoices.view'))
            <a class="nav-link {{ $nav('invoices.*') }}" href="{{ route('invoices.index') }}"><i class="bi bi-receipt"></i> {{ __('Invoices') }}</a>
        @endif

        @if($can('couriers.view') || $can('reports.view') || $can('branches.manage') || $can('users.manage') || $can('roles.manage'))
            <div class="nav-section">{{ __('Administration') }}</div>
        @endif
        @if($can('couriers.view'))
            <a class="nav-link {{ $nav('couriers.*') }}" href="{{ route('couriers.index') }}"><i class="bi bi-bicycle"></i> {{ __('Couriers') }}</a>
        @endif
        @if($can('reports.view'))
            <a class="nav-link {{ $nav('reports.*') }}" href="{{ route('reports.index') }}"><i class="bi bi-bar-chart-line"></i> {{ __('Reports') }}</a>
        @endif
        @if($can('branches.manage'))
            <a class="nav-link {{ $nav('branches.*') }}" href="{{ route('branches.index') }}"><i class="bi bi-diagram-3"></i> {{ __('Branches') }}</a>
        @endif
        @if($can('users.manage'))
            <a class="nav-link {{ $nav('users.*') }}" href="{{ route('users.index') }}"><i class="bi bi-person-gear"></i> {{ __('Users') }}</a>
        @endif
        @if($can('roles.manage'))
            <a class="nav-link {{ $nav('roles.*') }}" href="{{ route('roles.index') }}"><i class="bi bi-shield-lock"></i> {{ __('Roles and permissions') }}</a>
        @endif

        <div class="nav-section">{{ __('General') }}</div>
        <a class="nav-link {{ $nav('notifications.*') }}" href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> {{ __('Notifications') }}
            @if($unreadCount)<span class="badge bg-danger ms-auto">{{ $unreadCount }}</span>@endif
        </a>
        <a class="nav-link" target="_blank" href="{{ route('tracking') }}"><i class="bi bi-geo-alt"></i> {{ __('Public tracking page') }}</a>
        <a class="nav-link {{ $nav('profile.*') }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i> {{ __('My account') }}</a>
    </nav>
</aside>

<div class="main">
    <header class="topbar px-3 py-2 d-flex align-items-center gap-2">
        <button class="btn btn-light d-lg-none" onclick="toggleSidebar()"><i class="bi bi-list fs-5"></i></button>
        <form class="d-none d-md-flex flex-grow-1" action="{{ route('tracking') }}" method="get" target="_blank" style="max-width:380px">
            <div class="input-group input-group-sm">
                <input type="text" name="number" class="form-control" placeholder="{{ __('Track a shipment by tracking number') }}...">
                <button class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
            </div>
        </form>

        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="btn btn-light btn-sm fw-semibold" title="Language"><i class="bi bi-translate"></i> {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</a>
            <div class="dropdown">
                <button class="btn btn-light position-relative" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="bi bi-bell fs-5"></i>
                    @if($unreadCount)<span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-danger">{{ $unreadCount }}</span>@endif
                </button>
                <div class="dropdown-menu dropdown-menu-start p-0 shadow" style="width:340px">
                    <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                        <strong>{{ __('Notifications') }}</strong>
                        @if($unreadCount)
                            <form method="post" action="{{ route('notifications.readAll') }}">@csrf
                                <button class="btn btn-link btn-sm p-0">{{ __('Mark all as read') }}</button>
                            </form>
                        @endif
                    </div>
                    @forelse($recentNotifs as $n)
                        <a href="{{ route('notifications.read', $n->id) }}" class="dropdown-item py-2 {{ $n->read_at ? '' : 'bg-light fw-semibold' }}" style="white-space:normal">
                            <div class="small"><i class="bi bi-circle-fill text-{{ $n->type }}" style="font-size:.5rem"></i> {{ $n->title }}</div>
                            <div class="text-muted small">{{ $n->message }}</div>
                            <div class="text-muted" style="font-size:.7rem">{{ $n->created_at->diffForHumans() }}</div>
                        </a>
                    @empty
                        <div class="p-3 text-center text-muted small">{{ __('No notifications') }}</div>
                    @endforelse
                    <a href="{{ route('notifications.index') }}" class="dropdown-item text-center small border-top">{{ __('View all') }}</a>
                </div>
            </div>

            <div class="dropdown">
                <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span class="d-none d-sm-inline small text-start lh-sm">{{ $authUser->name }}<br><span class="text-muted">{{ $authUser->roleLabel() }} - {{ $authUser->workplaceLabel() }}</span></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-start">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-gear"></i> {{ __('My account') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="post" action="{{ route('logout') }}">@csrf
                            <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> {{ __('Log out') }}</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main class="p-3 p-lg-4">
        @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle"></i> {{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle"></i> {{ session('error') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if($errors->any())
            <div class="alert alert-danger"><strong>{{ __('There are errors in the data') }}:</strong>
                <ul class="mb-0 mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 no-print">
            <h4 class="mb-0">@yield('heading')</h4>
            <div class="d-flex gap-2 flex-wrap">@yield('actions')</div>
        </div>

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('overlay').classList.toggle('show');
    }
    document.querySelectorAll('form[data-confirm]').forEach(function (f) {
        f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
    });
</script>
@stack('scripts')
</body>
</html>
