<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin — FORMIVA' }}</title>
    @vite('resources/css/admin.css')
</head>
<body class="admin-body">
    <div class="admin-shell">
        <aside class="admin-sidebar" aria-label="Admin navigation">
            <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                <span class="admin-brand-mark">F</span>
                <span>FORMIVA <small>ADMIN</small></span>
            </a>
            <nav class="admin-nav">
                <a class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span class="admin-nav-label">Pipeline</span>
                <span class="admin-nav-placeholder">Inquiries <small>Coming later</small></span>
                <span class="admin-nav-label">Work</span>
                <span class="admin-nav-placeholder">Projects <small>Coming later</small></span>
                <span class="admin-nav-placeholder">Services <small>Coming later</small></span>
                <span class="admin-nav-label">Website</span>
                <span class="admin-nav-placeholder">Insights <small>Coming later</small></span>
                <span class="admin-nav-placeholder">Studio <small>Coming later</small></span>
                @if(auth()->user()->isAdmin())
                    <span class="admin-nav-label">System</span>
                    <span class="admin-nav-placeholder">Users <small>Coming later</small></span>
                @endif
            </nav>
            <div class="admin-sidebar-footer">
                <a href="{{ route('admin.profile.edit') }}">Profile</a>
                <a href="{{ url('/') }}">View public site</a>
            </div>
        </aside>
        <div class="admin-main-wrap">
            <header class="admin-header">
                <div>
                    <p class="admin-kicker">Internal workspace</p>
                    <p class="admin-header-title">{{ $header ?? 'Dashboard' }}</p>
                </div>
                <div class="admin-user-menu">
                    <span>{{ auth()->user()->name }}</span>
                    <span class="admin-role">{{ auth()->user()->role->value }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="admin-text-button">Sign out</button>
                    </form>
                </div>
            </header>
            <main class="admin-content">
                @if(session('status'))
                    <div class="admin-alert admin-alert-success" role="status">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="admin-alert admin-alert-error" role="alert">
                        <strong>Please check the highlighted fields.</strong>
                        <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
