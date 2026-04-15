@extends('volunteer ui.layout')

@section('title', 'Assigned Tasks - DisasterResponseHub')
@section('page-title', 'Assigned Tasks')
@section('page-subtitle', 'Review each disaster assignment with its location, incidents, and working hours.')

@section('content')
    <section class="stack-grid">
        @forelse ($tasks as $task)
            <article class="panel-card task-card">
                <div class="panel-header">
                    <div>
                        <h3>{{ $task->disaster_type }}</h3>
                        <p>{{ $task->city ?? 'Unknown city' }}, {{ $task->district ?? 'Unknown district' }}</p>
                    </div>
                    <span class="status-pill status-{{ $task->disaster_status }}">{{ $task->disaster_status }}</span>
                </div>

                <div class="task-meta-grid">
                    <div>
                        <span class="meta-label">Assigned date</span>
                        <strong>{{ $task->assigned_date }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Affected population</span>
                        <strong>{{ number_format($task->affected_population) }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Hours worked</span>
                        <strong>{{ $task->hours_worked }}</strong>
                    </div>
                    <div>
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

                <form method="POST" action="{{ route('volunteer.tasks.update-hours', $task->assignment_id) }}" class="inline-form">
                    @csrf
                    @method('PATCH')
                    <label for="hours_worked_{{ $task->assignment_id }}">Update hours worked</label>
                    <div class="inline-controls">
                        <input
                            id="hours_worked_{{ $task->assignment_id }}"
                            name="hours_worked"
                            type="number"
                            min="0"
                            max="1000"
                            value="{{ $task->hours_worked }}"
                            required
                        >
                        <button type="submit" class="primary-action">Save</button>
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