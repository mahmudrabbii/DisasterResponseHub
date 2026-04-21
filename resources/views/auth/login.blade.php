<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DisasterResponseHub</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/DRH Logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="container">
    <div class="logo">
        <img class="logo-mark" src="{{ asset('assets/DRH Logo.png') }}" alt="Disaster Response Hub Logo">
        <h2>Disaster Response Hub</h2>
    </div>
    <h1>Sign In</h1>
    <p class="subtitle">Enter your credentials to access your account</p>

    @if (session('status'))
        <div class="alert success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="errors">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>


        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>
        </div>


        <div class="form-group">
            <label for="role">Select User Type</label>
            <select id="role" name="role" required>
                <option value="">Select User Type</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="official" {{ old('role') === 'official' ? 'selected' : '' }}>NGO Official</option>
                <option value="volunteer" {{ old('role') === 'volunteer' ? 'selected' : '' }}>Volunteer</option>
            </select>
        </div>

        <button type="submit">Sign In</button>
    </form>

    <div class="link">
        Don't have an account? <a href="{{ route('register') }}">Register here</a>
    </div>
</div>
</body>
</html>
