<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - DisasterResponseHub')</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<div class="admin-shell" data-admin-shell>
    <aside class="admin-sidebar">
        <div class="brand-block">
            <div class="brand-mark">DRH</div>
            <div>
                <h2>Admin Panel</h2>
                <p>Operations and management</p>
            </div>
        </div>

        <nav class="nav-links">
            <a class="nav-link {{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="nav-link {{ ($activePage ?? '') === 'users' ? 'active' : '' }}" href="{{ route('admin.users') }}">Manage Users</a>
            <a class="nav-link {{ ($activePage ?? '') === 'disasters' ? 'active' : '' }}" href="{{ route('admin.disasters') }}">Disaster Management</a>
            <a class="nav-link {{ ($activePage ?? '') === 'volunteers' ? 'active' : '' }}" href="{{ route('admin.volunteers') }}">Volunteer List</a>
            <a class="nav-link {{ ($activePage ?? '') === 'resources' ? 'active' : '' }}" href="{{ route('admin.resources') }}">Resource Inventory</a>
            <a class="nav-link {{ ($activePage ?? '') === 'affected-people' ? 'active' : '' }}" href="{{ route('admin.affected-people') }}">Affected People</a>
            <a class="nav-link {{ ($activePage ?? '') === 'aid-requests' ? 'active' : '' }}" href="{{ route('admin.aid-requests') }}">Aid Requests</a>
        </nav>

        <div class="sidebar-card">
            <span class="sidebar-label">Signed in as</span>
            <strong>{{ optional(auth()->user()->person)->name ?? 'Admin' }}</strong>
            <span>{{ ucfirst(auth()->user()->role ?? 'admin') }}</span>
        </div>
    </aside>

    <div class="workspace">
        <header class="topbar">
            <button class="sidebar-toggle" type="button" data-toggle-sidebar>Menu</button>
            <div class="topbar-copy">
                <span class="eyebrow">Administration</span>
                <h1>@yield('page-title', 'Dashboard')</h1>
                <p>@yield('page-subtitle', 'Manage users, disasters, volunteers, stock, and affected people from one place.') </p>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
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
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var shell = document.querySelector('[data-admin-shell]');
        var button = document.querySelector('[data-toggle-sidebar]');

        if (shell && button) {
            button.addEventListener('click', function () {
                shell.classList.toggle('sidebar-open');
            });
        }
    });
</script>
</body>
</html>