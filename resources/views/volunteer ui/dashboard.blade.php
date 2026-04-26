@extends('volunteer ui.layout')

@section('title', 'Volunteer Dashboard - DisasterResponseHub')
@section('page-title', 'Volunteer Dashboard')
@section('page-subtitle', 'Track assignments, requests, and disaster activity from a single command center.')

@section('content')
    <section class="hero-panel">
        <div>
            <span class="section-kicker">Welcome back</span>
            <h2>{{ $person->name }}</h2>
            <p>Availability: <strong>{{ ucfirst(str_replace('_', ' ', $volunteer->availability ?? 'available')) }}</strong></p>
            <p>Skills: {{ $volunteer->skills ?? 'No skills profile saved yet.' }}</p>
        </div>
        <div class="hero-actions">
            <a href="{{ route('volunteer.tasks') }}" class="primary-action">Review assigned tasks</a>
            <a href="{{ route('volunteer.aid-requests') }}" class="secondary-action">Submit aid request</a>
        </div>
    </section>

    <section class="metrics-grid">
        <article class="metric-card">
            <span>Assigned tasks</span>
            <strong>{{ $stats['assigned_tasks'] }}</strong>
            <small>Active work items linked to your profile</small>
        </article>
        <article class="metric-card">
            <span>Assigned disasters</span>
            <strong>{{ $stats['unique_disasters'] }}</strong>
            <small>Distinct disaster operations you support</small>
        </article>
        <article class="metric-card">
            <span>Hours worked</span>
            <strong>{{ $stats['hours_worked'] }}</strong>
            <small>Total hours recorded on assignments</small>
        </article>
        <article class="metric-card">
            <span>Pending requests</span>
            <strong>{{ $stats['pending_requests'] }}</strong>
            <small>Aid requests waiting for review</small>
        </article>
    </section>

    <section class="panel-grid">
        <article class="panel-card">
            <div class="panel-header">
                <h3>Recent assignments</h3>
                <a href="{{ route('volunteer.tasks') }}">View all</a>
            </div>

            @forelse ($tasks->take(4) as $task)
                <div class="list-row">
                    <div>
                        <strong>{{ $task->disaster_type }}</strong>
                        <p>{{ $task->city ?? 'Unknown location' }}, {{ $task->district ?? 'Unknown district' }}</p>
                    </div>
                    <div class="row-meta">
                        <span>{{ $task->hours_worked }} hrs</span>
                        <small>{{ $task->disaster_status }}</small>
                    </div>
                </div>
            @empty
                <p class="empty-state">No assignments are linked to this volunteer profile yet.</p>
            @endforelse
        </article>

        <article class="panel-card">
            <div class="panel-header">
                <h3>Recent aid requests</h3>
                <a href="{{ route('volunteer.aid-requests') }}">Manage</a>
            </div>

            @forelse ($aidRequests->take(4) as $request)
                <div class="list-row">
                    <div>
                        <strong>{{ $request->aid_type ?? 'Aid request' }}</strong>
                        <p>{{ $request->city ?? 'Unknown city' }}, {{ $request->district ?? 'Unknown district' }}</p>
                    </div>
                    <div class="row-meta">
                        <span class="status-pill status-{{ $request->status }}">{{ $request->status }}</span>
                    </div>
                </div>
            @empty
                <p class="empty-state">No aid requests have been submitted yet.</p>
            @endforelse
        </article>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Disaster watchlist</h3>
            <a href="{{ route('volunteer.disaster-data') }}">Open disaster data</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Disaster</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Incidents</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($disasters->take(5) as $disaster)
                    <tr>
                        <td>
                            <strong>{{ $disaster->type }}</strong>
                            <div class="muted">{{ $disaster->disaster_date }}</div>
                        </td>
                        <td>{{ $disaster->city ?? 'Unknown' }}, {{ $disaster->district ?? 'Unknown' }}</td>
                        <td><span class="status-pill status-{{ $disaster->status }}">{{ $disaster->status }}</span></td>
                        <td>{{ $disasterIncidentCounts[$disaster->id] ?? 0 }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-state">No disaster records are available.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Official policy updates</h3>
            <span class="muted">Latest coordination guidance from NGO officials</span>
        </div>

        @forelse ($policies as $policy)
            <div class="list-row">
                <div>
                    <strong>{{ $policy->title }}</strong>
                    <p>{{ $policy->description }}</p>
                </div>
            </div>
        @empty
            <p class="empty-state">No policy updates available yet.</p>
        @endforelse
    </section>
@endsection