<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'NGO Official - DisasterResponseHub')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/DRH Logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/official.css') }}">
</head>
<body>
<div class="admin-shell" data-admin-shell>
    <input class="sidebar-toggle-state" type="checkbox" id="official-sidebar-toggle" aria-hidden="true">
    <aside class="admin-sidebar">
        <a class="brand-block" href="{{ route('official.dashboard') }}" aria-label="Go to official dashboard">
            <div class="brand-mark">
                <img class="brand-logo" src="{{ asset('assets/DRH Logo.png') }}" alt="DRH Logo">
            </div>
            <div>
                <h2>NGO Official</h2>
                <p>Response coordination workspace</p>
            </div>
        </a>

        <nav class="nav-links">
            <a class="nav-link {{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}" href="{{ route('official.dashboard') }}">Dashboard</a>
            <a class="nav-link {{ ($activePage ?? '') === 'disasters' ? 'active' : '' }}" href="{{ route('official.disasters') }}">Disaster Handling</a>
            <a class="nav-link {{ ($activePage ?? '') === 'donations' ? 'active' : '' }}" href="{{ route('official.donations') }}">Donations</a>
            <a class="nav-link {{ ($activePage ?? '') === 'volunteers' ? 'active' : '' }}" href="{{ route('official.volunteers') }}">Volunteer Coordination</a>
            <a class="nav-link {{ ($activePage ?? '') === 'volunteer-submissions' ? 'active' : '' }}" href="{{ route('official.volunteer-submissions') }}">Volunteer Records</a>
            <a class="nav-link {{ ($activePage ?? '') === 'resources' ? 'active' : '' }}" href="{{ route('official.resources') }}">Resource Handling</a>
            <a class="nav-link {{ ($activePage ?? '') === 'community-supports' ? 'active' : '' }}" href="{{ route('official.community-supports') }}">Community Support</a>
            <a class="nav-link {{ ($activePage ?? '') === 'policies' ? 'active' : '' }}" href="{{ route('official.policies') }}">Policies</a>
            <a class="nav-link {{ ($activePage ?? '') === 'public-disaster-reports' ? 'active' : '' }}" href="{{ route('official.public-disaster-reports') }}">Public Disaster Reports</a>
            <a class="nav-link {{ ($activePage ?? '') === 'public-help-requests' ? 'active' : '' }}" href="{{ route('official.public-help-requests') }}">Help Requests</a>
        </nav>

        <div class="sidebar-card">
            <strong>Signed in as {{ optional(auth()->user()->person)->name ?? 'Official' }}</strong>

            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </aside>

    <div class="workspace">
        <header class="topbar">
            <label class="sidebar-toggle" for="official-sidebar-toggle">Menu</label>
            <div class="topbar-copy">
                <h1>@yield('page-title', 'NGO Official Dashboard')</h1>
                <p>@yield('page-subtitle', 'Coordinate disaster response, volunteers, resources, and community support from one place.')</p>
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

            @if (!empty($alerts) && count($alerts) > 0)
                <div class="panel-card full-width">
                    <div class="panel-header">
                        <h3>Recent alerts</h3>
                        <span class="muted">Alerts are shown to the volunteers and rescue team</span>
                    </div>

                    <div class="alerts-grid">
                        @foreach ($alerts as $alert)
                            <div class="alert-card">
                                <h4>{{ $alert->title }}</h4>
                                <p>{{ $alert->message }}</p>
                                <span class="alert-time">{{ $alert->created_at }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="layout-footer">
            <p>&copy; {{ date('Y') }} Disaster Response Hub. All rights reserved.</p>
        </footer>
    </div>

    <label class="sidebar-backdrop" for="official-sidebar-toggle" aria-hidden="true"></label>
</div>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>