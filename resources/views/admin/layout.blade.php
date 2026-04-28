<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - DisasterResponseHub')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/DRH Logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<div class="admin-shell" data-admin-shell>
    <input class="sidebar-toggle-state" type="checkbox" id="admin-sidebar-toggle" aria-hidden="true">
    <aside class="admin-sidebar">
        <a class="brand-block" href="{{ route('admin.dashboard') }}" aria-label="Go to admin dashboard">
            <div class="brand-mark">
                <img class="brand-logo" src="{{ asset('assets/DRH Logo.png') }}" alt="DRH Logo">
            </div>
            <div>
                <h2>Admin Panel</h2>
                <p>Operations and management</p>
            </div>
        </a>

        <nav class="nav-links">
            <a class="nav-link {{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="nav-link {{ ($activePage ?? '') === 'users' ? 'active' : '' }}" href="{{ route('admin.users') }}">Manage Users</a>
            <a class="nav-link {{ ($activePage ?? '') === 'disasters' ? 'active' : '' }}" href="{{ route('admin.disasters') }}">Disaster Management</a>
            <a class="nav-link {{ ($activePage ?? '') === 'volunteers' ? 'active' : '' }}" href="{{ route('admin.volunteers') }}">Volunteer List</a>
            <a class="nav-link {{ ($activePage ?? '') === 'resources' ? 'active' : '' }}" href="{{ route('admin.resources') }}">Resource Inventory</a>
            <a class="nav-link {{ ($activePage ?? '') === 'affected-people' ? 'active' : '' }}" href="{{ route('admin.affected-people') }}">Affected People</a>
            <a class="nav-link {{ ($activePage ?? '') === 'aid-requests' ? 'active' : '' }}" href="{{ route('admin.aid-requests') }}">Aid Requests</a>
            <a class="nav-link {{ ($activePage ?? '') === 'disaster-submissions' ? 'active' : '' }}" href="{{ route('admin.disaster-submissions') }}">Volunteer Reports</a>
            <a class="nav-link {{ ($activePage ?? '') === 'public-disaster-reports' ? 'active' : '' }}" href="{{ route('admin.public-disaster-reports') }}">Public Disaster Reports</a>
            <a class="nav-link {{ ($activePage ?? '') === 'public-help-requests' ? 'active' : '' }}" href="{{ route('admin.public-help-requests') }}">Public Help Requests</a>
        </nav>

        <div class="sidebar-card">
            <strong>Signed in as {{ optional(auth()->user()->person)->name ?? 'Admin' }}</strong>

            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </aside>

    <div class="workspace">
        <header class="topbar">
            <label class="sidebar-toggle" for="admin-sidebar-toggle">Menu</label>
            <div class="topbar-copy">
                <!--
                <span class="eyebrow">Administration</span>
                -->
                <h1>@yield('page-title', 'Dashboard')</h1>
                <p>@yield('page-subtitle', 'Manage users, disasters, volunteers, stock and affected people from one place.') </p>
            </div>
        </header>

        <main class="content">
            @if (session('status'))
                <div class="status-banner">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="error-panel">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="layout-footer">
            <p>&copy; {{ date('Y') }} Disaster Response Hub. All rights reserved.</p>
        </footer>
    </div>

    <label class="sidebar-backdrop" for="admin-sidebar-toggle" aria-hidden="true"></label>
</div>
<script src="{{ asset('js/weather.js') }}"></script>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>