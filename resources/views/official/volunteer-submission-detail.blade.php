@extends('official.layout')

@section('title', $submission->title . ' - DisasterResponseHub')
@section('page-title', $submission->title)
@section('page-subtitle', 'Review and manage this volunteer submission')

@section('content')
    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Submission Details</h3>
            <a href="{{ route('official.volunteer-submissions') }}">Back to list</a>
        </div>

        <div class="panel-grid">
            <!-- Details Section -->
            <div class="list-row">
                <strong>Submitted by</strong>
                <p>{{ $submission->volunteer_name }} ({{ $submission->volunteer_email }})</p>
            </div>

            <div class="list-row">
                <strong>Disaster</strong>
                <p>{{ $submission->disaster_type }} - {{ $submission->city }}, {{ $submission->district }}<br><small>{{ $submission->disaster_date }}</small></p>
            </div>

            <div class="list-row">
                <strong>Submission Type</strong>
                <p><span class="status-pill status-pending">{{ str_replace('_', ' ', ucfirst($submission->submission_type)) }}</span></p>
            </div>

            <div class="list-row">
                <strong>Status</strong>
                <p><span class="status-pill status-{{ $submission->status }}">{{ ucfirst($submission->status) }}</span></p>
            </div>

            <div class="list-row">
                <strong>Submitted</strong>
                <p>{{ $submission->created_at->format('M d, Y \a\t H:i') }}</p>
            </div>

            <div class="list-row">
                <strong>Last Updated</strong>
                <p>{{ $submission->updated_at->format('M d, Y \a\t H:i') }}</p>
            </div>
        </div>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Submission Description</h3>
        </div>

        <div class="section-description">
            <p class="description-text">{{ $submission->description }}</p>
        </div>
    </section>

    <section class="panel-card">
        <div class="panel-header">
            <h3>Review Submission</h3>
        </div>

        <form method="POST" action="{{ route('official.volunteer-submissions.update', $submission->id) }}" class="form-grid">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="status">Update Status</label>
                <select id="status" name="status" required>
                    <option value="pending" @selected('pending')>Pending</option>
                    <option value="approved" @selected('approved')>Approved</option>
                    <option value="rejected" @selected('rejected')>Rejected</option>
                </select>
            </div>

            <div class="form-group form-wide">
                <label for="admin_notes">Admin Notes</label>
                <textarea id="admin_notes" name="admin_notes" rows="6" maxlength="5000">{{ $submission->admin_notes }}</textarea>
            </div>

            <div class="form-actions form-wide">
                <button type="submit" class="primary-action">Save Review</button>
                <a href="{{ route('official.volunteer-submissions') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
