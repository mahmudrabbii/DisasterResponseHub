<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>@yield('title', 'Volunteer Hub - DisasterResponseHub')</title>
	<link rel="icon" type="image/png" href="{{ asset('assets/DRH Logo.png') }}">
	<link rel="stylesheet" href="{{ asset('css/volunteer.css') }}">
</head>
<body>
<div class="volunteer-shell" data-volunteer-shell>
	<aside class="sidebar">
		<a href="{{ route('volunteer.dashboard') }}" class="brand-block-link">
			<div class="brand-block">
				<div class="brand-mark">
					<img class="brand-logo" src="{{ asset('assets/DRH Logo.png') }}" alt="DRH Logo">
				</div>
				<div>
					<h2>Volunteer Hub</h2>
					<p>Relief coordination workspace</p>
				</div>
			</div>
		</a>

		<nav class="nav-links">
			<a class="nav-link {{ ($activePage ?? '') === 'dashboard' ? 'active' : '' }}" href="{{ route('volunteer.dashboard') }}">Volunteer Dashboard</a>
			<a class="nav-link {{ ($activePage ?? '') === 'tasks' ? 'active' : '' }}" href="{{ route('volunteer.tasks') }}">Assigned Tasks</a>
			<a class="nav-link {{ ($activePage ?? '') === 'profile' ? 'active' : '' }}" href="{{ route('volunteer.profile') }}">Profile</a>
			<a class="nav-link {{ ($activePage ?? '') === 'aid-request' ? 'active' : '' }}" href="{{ route('volunteer.aid-requests') }}">Aid Request</a>
			<a class="nav-link {{ ($activePage ?? '') === 'disaster-data' ? 'active' : '' }}" href="{{ route('volunteer.disaster-data') }}">Disaster Details</a>
			<a class="nav-link {{ ($activePage ?? '') === 'disaster-submissions' ? 'active' : '' }}" href="{{ route('volunteer.disaster-submissions') }}">Submit Disaster Report</a>
		</nav>

		<div class="sidebar-card">
			<strong>Signed in as {{ optional(auth()->user()->person)->name ?? 'Volunteer' }}</strong>

			<form method="POST" action="{{ route('logout') }}" class="logout-form">
				@csrf
				<button type="submit" class="logout-btn">Logout</button>
			</form>
		</div>
	</aside>

	<div class="workspace">
		<header class="topbar">
			<button class="sidebar-toggle" type="button" data-toggle-sidebar>Menu</button>
			<div class="topbar-copy">
				<span class="eyebrow">Volunteer operations</span>
				<h1>@yield('page-title', 'Dashboard')</h1>
				<p>@yield('page-subtitle', 'Manage your assignments, profile, and relief requests from one place.') </p>
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
				<div class="alerts-section">
					<h3 class="alerts-title">Recent Alerts</h3>
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
</div>

<script src="{{ asset('js/sidebar-toggle.js') }}"></script>
</body>
</html>
