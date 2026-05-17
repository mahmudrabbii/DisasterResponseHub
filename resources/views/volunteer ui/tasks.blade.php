@extends('volunteer ui.layout')

@section('title', 'Assigned Tasks - DisasterResponseHub')
@section('page-title', 'Assigned Tasks')
@section('page-subtitle', 'Review each disaster assignment and accept work to confirm your participation.')

@section('content')
    <section class="stack-grid">
        @forelse ($tasks as $task)
            <article class="panel-card task-card">
                <div class="panel-header task-header">
                    <div class="task-heading">
                        <span class="task-kicker">Assigned Disaster</span>
                        <h3>{{ $task->disaster_type }}</h3>
                        <p class="task-location">{{ $task->city ?? 'Unknown city' }}, {{ $task->district ?? 'Unknown district' }}</p>
                    </div>
                    <span class="status-pill status-{{ $task->disaster_status }}">{{ ucfirst(str_replace('_', ' ', $task->disaster_status)) }}</span>
                </div>

                <div class="task-summary-line">
                    <strong>Disaster Snapshot</strong>
                    <span>{{ ($taskIncidents[$task->disaster_id] ?? collect())->count() }} linked incidents</span>
                </div>

                <div class="task-meta-grid">
                    <div class="task-fact">
                        <span class="meta-label">Assigned date</span>
                        <strong>{{ $task->assigned_date }}</strong>
                    </div>
                    <div class="task-fact">
                        <span class="meta-label">Affected population</span>
                        <strong>{{ number_format($task->affected_population) }}</strong>
                    </div>
                    <div class="task-fact">
                        <span class="meta-label">Hours worked</span>
                        <strong>{{ $task->hours_worked }} hrs</strong>
                    </div>
                    <div class="task-fact">
                        <span class="meta-label">Disaster date</span>
                        <strong>{{ $task->disaster_date }}</strong>
                    </div>
                </div>

                <div class="incident-list">
                    <h4>Linked incidents</h4>
                    @forelse (($taskIncidents[$task->disaster_id] ?? collect()) as $incident)
                        <div class="incident-item">
                            <strong>{{ $incident->title }}</strong>
                            <p>{{ $incident->description }}</p>
                            <small>Severity: {{ $incident->severity }} | Status: {{ $incident->status }}</small>
                        </div>
                    @empty
                        <p class="empty-state">No incidents recorded for this disaster yet.</p>
                    @endforelse
                </div>

                @php
                    $isAccepted = in_array((int) $task->assignment_id, $acceptedAssignments ?? [], true);
                @endphp

                <form method="POST" action="{{ route('volunteer.tasks.accept', $task->assignment_id) }}" class="inline-form">
                    @csrf
                    <label>Work status</label>
                    <div class="inline-controls">
                        @if ($isAccepted)
                            <span class="status-pill status-completed">Accepted</span>
                        @else
                            <button type="submit" class="primary-action">Accept Work</button>
                        @endif
                    </div>
                </form>
            </article>
        @empty
            <article class="panel-card">
                <p class="empty-state">No assigned tasks found for this volunteer.</p>
            </article>
        @endforelse
    </section>
@endsection