@extends('volunteer ui.layout')

@section('title', 'Disaster Data - DisasterResponseHub')
@section('page-title', 'Disaster Data')
@section('page-subtitle', 'Monitor active, pending, and resolved disaster records with linked incidents.')

@section('content')
    <section class="filter-bar">
        <a class="filter-chip {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('volunteer.disaster-data', ['status' => 'all']) }}">All</a>
        <a class="filter-chip {{ $filter === 'pending' ? 'active' : '' }}" href="{{ route('volunteer.disaster-data', ['status' => 'pending']) }}">Pending</a>
        <a class="filter-chip {{ $filter === 'in_progress' ? 'active' : '' }}" href="{{ route('volunteer.disaster-data', ['status' => 'in_progress']) }}">In progress</a>
        <a class="filter-chip {{ $filter === 'resolved' ? 'active' : '' }}" href="{{ route('volunteer.disaster-data', ['status' => 'resolved']) }}">Resolved</a>
    </section>

    <section class="metrics-grid compact-metrics">
        <article class="metric-card">
            <span>Active disasters</span>
            <strong>{{ $stats['active_disasters'] }}</strong>
            <small>Disasters currently in progress</small>
        </article>
        <article class="metric-card">
            <span>Tracked incidents</span>
            <strong>{{ $disasters->sum(fn ($item) => (int) ($disasterIncidentCounts[$item->id] ?? 0)) }}</strong>
            <small>Incident records linked to filtered disasters</small>
        </article>
    </section>

    <section class="stack-grid">
        @forelse ($disasters as $disaster)
            <article class="panel-card disaster-card">
                <div class="panel-header">
                    <div>
                        <h3>{{ $disaster->type }}</h3>
                        <p>{{ $disaster->city ?? 'Unknown city' }}, {{ $disaster->district ?? 'Unknown district' }}</p>
                    </div>
                    <span class="status-pill status-{{ $disaster->status }}">{{ $disaster->status }}</span>
                </div>

                <div class="task-meta-grid">
                    <div>
                        <span class="meta-label">Disaster date</span>
                        <strong>{{ $disaster->disaster_date }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Affected population</span>
                        <strong>{{ number_format($disaster->affected_population) }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Incident count</span>
                        <strong>{{ $disasterIncidentCounts[$disaster->id] ?? 0 }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Created</span>
                        <strong>{{ $disaster->created_at }}</strong>
                    </div>
                </div>

                <div class="incident-list">
                    <h4>Linked incidents</h4>
                    @forelse (($incidentFeed[$disaster->id] ?? collect()) as $incident)
                        <div class="incident-item">
                            <strong>{{ $incident->title }}</strong>
                            <p>{{ $incident->description }}</p>
                            <small>Severity: {{ $incident->severity }} | Status: {{ $incident->status }}</small>
                        </div>
                    @empty
                        <p class="empty-state">No incidents have been recorded for this disaster yet.</p>
                    @endforelse
                </div>
            </article>
        @empty
            <article class="panel-card">
                <p class="empty-state">No disaster records match the selected filter.</p>
            </article>
        @endforelse
    </section>
@endsection