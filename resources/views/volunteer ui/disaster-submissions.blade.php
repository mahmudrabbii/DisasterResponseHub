@extends('volunteer ui.layout')

@section('title', 'My Disaster Reports - DisasterResponseHub')
@section('page-title', 'My Disaster Reports')
@section('page-subtitle', 'Track the status of your submitted field data and reports.')

@section('content')
    <section class="submissions-header">
        <a href="{{ route('volunteer.disaster-submissions.create') }}" class="primary-action">+ Submit New Report</a>
    </section>

    @if (session('success'))
        <div class="status-banner success">
            {{ session('success') }}
        </div>
    @endif

    <section class="filter-bar">
        <a class="filter-chip {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('volunteer.disaster-submissions', ['status' => 'all']) }}">All</a>
        <a class="filter-chip {{ $filter === 'pending' ? 'active' : '' }}" href="{{ route('volunteer.disaster-submissions', ['status' => 'pending']) }}">Pending Review</a>
        <a class="filter-chip {{ $filter === 'approved' ? 'active' : '' }}" href="{{ route('volunteer.disaster-submissions', ['status' => 'approved']) }}">Approved</a>
        <a class="filter-chip {{ $filter === 'rejected' ? 'active' : '' }}" href="{{ route('volunteer.disaster-submissions', ['status' => 'rejected']) }}">Rejected</a>
    </section>

    <section class="submissions-grid">
        @forelse ($submissions as $submission)
            <article class="submission-card">
                <div class="submission-header">
                    <div>
                        <h3>{{ $submission->title }}</h3>
                        <p class="submission-disaster">
                            {{ $submission->disaster->type }} - {{ $submission->disaster->city ?? 'Unknown' }}, {{ $submission->disaster->district ?? 'Unknown' }}
                        </p>
                    </div>
                    <span class="status-pill status-{{ $submission->status }}">
                        {{ ucfirst($submission->status) }}
                    </span>
                </div>

                <div class="submission-meta">
                    <div>
                        <span class="meta-label">Report Type</span>
                        <strong>{{ str_replace('_', ' ', ucfirst($submission->submission_type)) }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Submitted</span>
                        <strong>{{ $submission->created_at->format('M d, Y') }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Updated</span>
                        <strong>{{ $submission->updated_at->format('M d, Y') }}</strong>
                    </div>
                </div>

                <div class="submission-description">
                    <h4>Description</h4>
                    <p>{{ Str::limit($submission->description, 300) }}</p>
                </div>

                @if ($submission->admin_notes)
                    <div class="admin-feedback">
                        <h4>Admin Feedback</h4>
                        <p>{{ $submission->admin_notes }}</p>
                    </div>
                @endif

                <div class="submission-actions">
                    <a href="{{ route('volunteer.disaster-submissions.show', $submission->id) }}" class="view-link">View Full Report</a>
                </div>
            </article>
        @empty
            <article class="submission-card">
                <div class="empty-state-container">
                    <p class="empty-state">No reports found.</p>
                    <p>Start contributing by <a href="{{ route('volunteer.disaster-submissions.create') }}">submitting your first report</a>.</p>
                </div>
            </article>
        @endforelse
    </section>
@endsection
