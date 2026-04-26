@extends('official.layout')

@section('title', 'Volunteer Coordination - DisasterResponseHub')
@section('page-title', 'Volunteer Coordination')
@section('page-subtitle', 'Assign volunteers to disaster operations and monitor activity.')

@section('content')
    <section class="panel-grid">
        <article class="panel-card">
            <div class="panel-header">
                <h3>Assign volunteer</h3>
            </div>

            <form method="POST" action="{{ route('official.volunteer-assignments.store') }}" class="stack-form">
                @csrf
                <div class="form-group">
                    <label for="person_id">Volunteer</label>
                    <select id="person_id" name="person_id" required>
                        <option value="">Select volunteer</option>
                        @foreach ($volunteers as $volunteer)
                            <option value="{{ $volunteer->person_id }}">{{ $volunteer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="disaster_id">Disaster</label>
                    <select id="disaster_id" name="disaster_id" required>
                        <option value="">Select disaster</option>
                        @foreach ($disasters as $disaster)
                            <option value="{{ $disaster->id }}">{{ $disaster->type }} - {{ $disaster->city ?? 'Unknown city' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="assigned_date">Assigned date</label>
                    <input id="assigned_date" name="assigned_date" type="date" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label for="hours_worked">Hours worked</label>
                    <input id="hours_worked" name="hours_worked" type="number" min="0" value="0">
                </div>
                <button type="submit" class="primary-action">Assign volunteer</button>
            </form>
        </article>

        <article class="panel-card">
            <div class="panel-header">
                <h3>Volunteer list</h3>
                <span class="muted">Current skills and availability</span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Skills</th>
                        <th>Availability</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($volunteers as $volunteer)
                        <tr>
                            <td>{{ $volunteer->name }}</td>
                            <td>{{ $volunteer->skills ?? 'N/A' }}</td>
                            <td>{{ $volunteer->availability }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-state">No volunteers found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Assigned volunteers</h3>
            <span class="muted">Recent assignment history</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Volunteer</th>
                    <th>Disaster</th>
                    <th>Location</th>
                    <th>Hours</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($volunteerAssignments as $assignment)
                    <tr>
                        <td>{{ $assignment->volunteer_name }}</td>
                        <td>{{ $assignment->disaster_type }}</td>
                        <td>{{ $assignment->city ?? 'N/A' }}, {{ $assignment->district ?? 'N/A' }}</td>
                        <td>{{ $assignment->hours_worked }}</td>
                        <td>{{ $assignment->assigned_date }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No assignments found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection