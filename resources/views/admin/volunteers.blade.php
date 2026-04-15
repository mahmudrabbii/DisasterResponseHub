@extends('admin.layout')

@section('title', 'Volunteer Management - DisasterResponseHub')
@section('page-title', 'Volunteer List Management')
@section('page-subtitle', 'View volunteer skills and assign volunteers to disasters.')

@section('content')
    @php
        $editingVolunteer = null;
        if (request()->filled('edit')) {
            $editingVolunteer = $volunteers->firstWhere('volunteer_id', (int) request('edit'));
        }
    @endphp

    <section class="panel-grid">
        <article class="panel-card">
            <div class="panel-header">
                <h3>{{ $editingVolunteer ? 'Edit volunteer profile' : 'Volunteer profile' }}</h3>
                @if ($editingVolunteer)
                    <a href="{{ route('admin.volunteers') }}">Cancel edit</a>
                @endif
            </div>

            @if ($editingVolunteer)
                <form method="POST" action="{{ route('admin.volunteers.update', $editingVolunteer->volunteer_id) }}" class="stack-form">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" value="{{ $editingVolunteer->name }}" disabled>
                    </div>

                    <div class="form-group">
                        <label for="skills">Skills</label>
                        <input id="skills" name="skills" type="text" value="{{ old('skills', $editingVolunteer->skills ?? '') }}">
                    </div>

                    <div class="form-group">
                        <label for="availability">Availability</label>
                        @php($availabilityValue = old('availability', $editingVolunteer->availability ?? 'available'))
                        <select id="availability" name="availability" required>
                            <option value="available" {{ $availabilityValue === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="busy" {{ $availabilityValue === 'busy' ? 'selected' : '' }}>Busy</option>
                            <option value="on_call" {{ $availabilityValue === 'on_call' ? 'selected' : '' }}>On call</option>
                            <option value="offline" {{ $availabilityValue === 'offline' ? 'selected' : '' }}>Offline</option>
                        </select>
                    </div>

                    <button type="submit" class="primary-action">Update volunteer</button>
                </form>
            @else
                <p class="empty-state">Choose Edit from the table to update a volunteer profile.</p>
            @endif
        </article>

        <article class="panel-card">
            <div class="panel-header">
                <h3>Assign volunteer to disaster</h3>
            </div>

            <form method="POST" action="{{ route('admin.volunteer-assignments.store') }}" class="stack-form">
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
                            <option value="{{ $disaster->id }}">{{ $disaster->type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="hours_worked">Hours worked</label>
                    <input id="hours_worked" name="hours_worked" type="number" min="0" value="0">
                </div>

                <div class="form-group">
                    <label for="assigned_date">Assigned date</label>
                    <input id="assigned_date" name="assigned_date" type="date" value="{{ date('Y-m-d') }}" required>
                </div>

                <button type="submit" class="primary-action">Assign volunteer</button>
            </form>
        </article>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Volunteer list</h3>
            <span class="muted">{{ $volunteers->count() }} records</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Skills</th>
                    <th>Availability</th>
                    <th>Assignments</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($volunteers as $volunteer)
                    <tr>
                        <td>{{ $volunteer->name }}</td>
                        <td>{{ $volunteer->phone ?? 'N/A' }}</td>
                        <td>{{ $volunteer->skills ?? 'N/A' }}</td>
                        <td><span class="status-pill status-{{ $volunteer->availability }}">{{ $volunteer->availability }}</span></td>
                        <td>{{ $volunteer->assignment_count }}</td>
                        <td class="actions-cell">
                            <a class="action-link" href="{{ route('admin.volunteers', ['edit' => $volunteer->volunteer_id]) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No volunteers found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Current assignments</h3>
            <span class="muted">{{ $assignments->count() }} records</span>
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
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->volunteer_name }}</td>
                        <td>{{ $assignment->disaster_name }}</td>
                        <td>{{ $assignment->city ?? 'N/A' }}, {{ $assignment->district ?? 'N/A' }}</td>
                        <td>{{ $assignment->hours_worked }}</td>
                        <td>{{ $assignment->assigned_date }}</td>
                        <td class="actions-cell">
                            <form method="POST" action="{{ route('admin.volunteer-assignments.destroy', $assignment->assignment_id) }}" class="inline-form" onsubmit="return confirm('Remove this assignment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="danger-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No assignments found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection