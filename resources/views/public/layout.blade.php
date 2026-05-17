<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Disaster Response Hub - Report and Get Help')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/DRH Logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/public.css') }}">
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
    @stack('styles')
</head>
<body>
<div class="admin-shell" data-admin-shell>
    <input class="sidebar-toggle-state" type="checkbox" id="public-sidebar-toggle" aria-hidden="true">
    <aside class="admin-sidebar">
        <a class="brand-block" href="{{ route('public.home') }}" aria-label="Go to public home">
            <div class="brand-mark">
                <img class="brand-logo" src="{{ asset('assets/DRH Logo.png') }}" alt="DRH Logo">
            </div>
            <div>
                <h2>Public User</h2>
                <p>Report and get help</p>
            </div>
        </a>

        <nav class="nav-links">
            <a class="nav-link {{ ($activePage ?? '') === 'home' ? 'active' : '' }}" href="{{ route('public.home') }}">Home</a>
           <!--
            <a class="nav-link {{ ($activePage ?? '') === 'disasters' ? 'active' : '' }}" href="{{ route('public.disasters') }}">View Disasters</a>
            
        
            <a class="nav-link {{ ($activePage ?? '') === 'alerts' ? 'active' : '' }}" href="{{ route('public.alerts') }}">Public Alerts</a>
            -->
            <a class="nav-link {{ ($activePage ?? '') === 'report-disaster' ? 'active' : '' }}" href="{{ route('public.report-disaster') }}">Report Disaster</a>
            <a class="nav-link {{ ($activePage ?? '') === 'request-help' ? 'active' : '' }}" href="{{ route('public.request-help') }}">Request Help</a>
            <a class="nav-link {{ ($activePage ?? '') === 'donate' ? 'active' : '' }}" href="{{ route('public.donate') }}">💰 Donate</a>
        </nav>

        <div class="sidebar-card">
            @auth
                <strong>Signed in as {{optional(auth()->user()->person)->name ?? 'User' }}</strong>
                <form method="POST" action="{{ route('logout') }}" class="logout-form">
                    @csrf
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            @endauth
            @guest
                <strong>Not signed in</strong>
                <div class="guest-auth">
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Register</a>
                </div>
            @endguest
        </div>
    </aside>

    <div class="workspace">
        {{-- 
        <header class="topbar">
            <label class="sidebar-toggle" for="public-sidebar-toggle">Menu</label>
            <div class="topbar-copy">
                <h1>@yield('page-title', 'Disaster Response Hub')</h1>
                <p>@yield('page-subtitle', 'Report disasters and request help in your community.')</p>
            </div>
        </header>
       --}}
        <main class="content">
            @if (session('status'))
                <div class="status-banner" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="error-panel" role="alert">
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

    <label class="sidebar-backdrop" for="public-sidebar-toggle" aria-hidden="true"></label>
</div>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
