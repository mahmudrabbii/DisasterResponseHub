@extends('volunteer ui.layout')

@section('title', 'Disaster Report - DisasterResponseHub')
@section('page-title', $submission->title)
@section('page-subtitle', 'View details of your submitted report')

@section('content')
    <div class="submission-detail-header">
        <div>
            <span class="breadcrumb">
                <a href="{{ route('volunteer.disaster-submissions') }}">My Disaster Reports</a>
                /
                {{ $submission->title }}
            </span>
        </div>
        <span class="status-pill status-{{ $submission->status }}">
            {{ ucfirst($submission->status) }}
        </span>
    </div>

    <div class="submission-detail-grid">
        <article class="submission-detail-panel">
            <section>
                <h2>{{ $submission->title }}</h2>
                <p class="submission-disaster">
                    {{ $submission->disaster->type }} - {{ $submission->disaster->city ?? 'Unknown' }}, {{ $submission->disaster->district ?? 'Unknown' }}
                </p>
            </section>

            <section class="detail-section">
                <h3>Report Information</h3>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Report Type</span>
                        <strong>{{ str_replace('_', ' ', ucfirst($submission->submission_type)) }}</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Disaster Date</span>
                        <strong>{{ $submission->disaster->disaster_date }}</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Submitted</span>
                        <strong>{{ $submission->created_at->format('M d, Y \a\t H:i') }}</strong>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Last Updated</span>
                        <strong>{{ $submission->updated_at->format('M d, Y \a\t H:i') }}</strong>
                    </div>
                </div>
            </section>

            <section class="detail-section">
                <h3>Description</h3>
                <div class="description-content">
                    {!! nl2br(e($submission->description)) !!}
                </div>
            </section>

            @if ($submission->admin_notes)
                <section class="detail-section admin-notes-section">
                    <h3>Admin Review</h3>
                    <div class="admin-notes">
                        <p><strong>Status:</strong> {{ ucfirst($submission->status) }}</p>
                        <p><strong>Feedback:</strong></p>
                        <div class="notes-content">
                            {!! nl2br(e($submission->admin_notes)) !!}
                        </div>
                    </div>
                </section>
            @endif
        </article>

        <aside class="submission-detail-sidebar">
            <div class="sidebar-card">
                <h4>Disaster Details</h4>
                <div class="sidebar-content">
                    <div class="sidebar-item">
                        <span class="label">Type</span>
                        <strong>{{ $submission->disaster->type }}</strong>
                    </div>
                    <div class="sidebar-item">
                        <span class="label">Location</span>
                        <strong>
                            {{ $submission->disaster->city ?? 'Unknown' }}<br>
                            {{ $submission->disaster->district ?? 'Unknown' }}
                        </strong>
                    </div>
                    <div class="sidebar-item">
                        <span class="label">Affected Population</span>
                        <strong>{{ number_format($submission->disaster->affected_population) }}</strong>
                    </div>
                    <div class="sidebar-item">
                        <span class="label">Status</span>
                        <strong>{{ ucfirst($submission->disaster->status) }}</strong>
                    </div>
                </div>
            </div>

            <div class="sidebar-card">
                <h4>Your Report Status</h4>
                <div class="sidebar-content">
                    @if ($submission->status === 'pending')
                        <p class="status-message pending-message">
                            Your report is under review by the admin team. This typically takes 1-2 days.
                        </p>
                    @elseif ($submission->status === 'approved')
                        <p class="status-message approved-message">
                            ✓ Your report has been approved and added to the disaster record.
                        </p>
                    @else
                        <p class="status-message rejected-message">
                            Your report was not approved. Please review the admin feedback above.
                        </p>
                    @endif
                </div>
            </div>

            <div class="sidebar-actions">
                <a href="{{ route('volunteer.disaster-submissions') }}" class="btn-secondary">Back to Reports</a>
            </div>
        </aside>
    </div>
@endsection
