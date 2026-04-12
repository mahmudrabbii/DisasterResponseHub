<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DisasterResponseHub</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Dashboard</h1>
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif

    <div class="card">
        <h2>Welcome Back</h2>
        <div class="info-row">
            <div class="info-item">
                <div class="info-label">Name</div>
                <div class="info-value">{{ auth()->user()->person->name ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value">{{ auth()->user()->person->email ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Phone</div>
                <div class="info-value">{{ auth()->user()->person->phone ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Role</div>
                <div class="info-value">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
