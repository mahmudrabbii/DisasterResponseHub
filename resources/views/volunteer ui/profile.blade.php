@extends('volunteer ui.layout')

@section('title', 'Volunteer Profile - DisasterResponseHub')
@section('page-title', 'Volunteer Profile')
@section('page-subtitle', 'Keep your contact details, skills, and availability up to date.')

@section('content')
    <section class="panel-grid profile-grid">
        <article class="panel-card">
            <h3>Profile details</h3>

            <form method="POST" action="{{ route('volunteer.profile.update') }}" class="stack-form">
                @csrf
                @method('PATCH')

                <label for="name">Full name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $person->name) }}" required>

                <label for="email">Email address</label>
                <input id="email" type="email" name="email" value="{{ old('email', $person->email) }}" required>

                <label for="phone">Phone number</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $person->phone) }}" required>

                <label for="skills">Skills</label>
                <input id="skills" type="text" name="skills" value="{{ old('skills', $volunteer->skills ?? '') }}" placeholder="First aid, rescue, logistics">

                <label for="availability">Availability</label>
                <select id="availability" name="availability" required>
                    @php($availability = old('availability', $volunteer->availability ?? 'available'))
                    <option value="available" {{ $availability === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="busy" {{ $availability === 'busy' ? 'selected' : '' }}>Busy</option>
                    <option value="on_call" {{ $availability === 'on_call' ? 'selected' : '' }}>On call</option>
                    <option value="offline" {{ $availability === 'offline' ? 'selected' : '' }}>Offline</option>
                </select>

                <button type="submit" class="primary-action">Update profile</button>
            </form>
        </article>

        <article class="panel-card summary-card">
            <h3>Live summary</h3>

            <div class="summary-item">
                <span>Assigned tasks</span>
                <strong>{{ $stats['assigned_tasks'] }}</strong>
            </div>
            <div class="summary-item">
                <span>Pending requests</span>
                <strong>{{ $stats['pending_requests'] }}</strong>
            </div>
            <div class="summary-item">
                <span>Hours worked</span>
                <strong>{{ $stats['hours_worked'] }}</strong>
            </div>
            <div class="summary-item">
                <span>Support status</span>
                <strong>{{ ucfirst(str_replace('_', ' ', $volunteer->availability ?? 'available')) }}</strong>
            </div>

            <div class="summary-note">
                Keep your profile accurate so coordinators can assign the right field work and contact you quickly during active relief operations.
            </div>
        </article>
    </section>
@endsection