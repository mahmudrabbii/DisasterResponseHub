<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DisasterResponseHub</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/DRH Logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="container register-container">
    <div class="logo">
        <img class="logo-mark" src="{{ asset('assets/DRH Logo.png') }}" alt="Disaster Response Hub Logo">
        <h2>DisasterResponseHub</h2>
    </div>
    <h1>Create Account</h1>
    <p class="subtitle">Register and join the relief network</p>

    @if ($errors->any())
        <div class="errors">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register.submit') }}">
        @csrf

        <div class="form-group">
            <label for="name">Full Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required>
        </div>

        <div class="form-group">
            <label for="role">Select Role</label>
            <select id="role" name="role" required>
                <option value="">Select Role</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="official" {{ old('role') === 'official' ? 'selected' : '' }}>NGO Official</option>
                <option value="volunteer" {{ old('role') === 'volunteer' ? 'selected' : '' }}>Volunteer</option>
            </select>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required>
        </div>

        <button type="submit">Register</button>
    </form>

    <div class="link">
        Already have an account? <a href="{{ route('login') }}">Sign in here</a>
    </div>
</div>
</body>
</html>
