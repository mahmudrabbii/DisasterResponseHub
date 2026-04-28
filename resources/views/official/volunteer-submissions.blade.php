@extends('official.layout')

@section('title', 'Volunteer Submissions - DisasterResponseHub')
@section('page-title', 'Volunteer Disaster Records')
@section('page-subtitle', 'Review incident reports, assessments, and data submitted by volunteers.')

@section('content')
    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Volunteer Submissions</h3>
            <span class="muted">{{ $volunteerSubmissions->count() }} submission(s)</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Volunteer</th>
                    <th>Disaster</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($volunteerSubmissions as $submission)
                    <tr>
                        <td>
                            <strong>{{ $submission->volunteer_name }}</strong>
                            <div class="muted">{{ $submission->volunteer_email }}</div>
                        </td>
                        <td>
                            <strong>{{ $submission->disaster_type }}</strong>
                            <div class="muted">{{ $submission->city ?? 'Unknown' }}, {{ $submission->district ?? 'Unknown' }}</div>
                        </td>
                        <td><span class="submission-type-pill">{{ str_replace('_', ' ', ucfirst($submission->submission_type)) }}</span></td>
                        <td><strong>{{ $submission->title }}</strong></td>
                        <td><span class="status-pill status-{{ $submission->status }}">{{ ucfirst($submission->status) }}</span></td>
                        <td>{{ $submission->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('official.volunteer-submissions.show', $submission->id) }}" class="action-link">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">No volunteer submissions available yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <style>
        .submission-type-pill {
            display: inline-block;
            padding: 4px 8px;
            background-color: #e8f4f8;
            color: #0066cc;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 500;
        }
    </style>
@endsection
