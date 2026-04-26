@extends('admin.layout')

@section('title', 'Review Submission - DisasterResponseHub')
@section('page-title', 'Review Volunteer Report')
@section('page-subtitle', 'Review and approve/reject the submitted disaster data.')

@section('content')
    <div class="review-header">
        <div>
            <a href="{{ route('admin.disaster-submissions') }}" class="back-link"> ← Back to Reports</a>
            <h2>{{ $submission->title }}</h2>
            <p class="submission-by">Submitted by <strong>{{ $submission->person->name }}</strong> on {{ $submission->created_at->format('M d, Y') }}</p>
        </div>
        <span class="status-pill status-{{ $submission->status }}">
            {{ ucfirst($submission->status) }}
        </span>
    </div>

    <div class="review-grid">
        <article class="review-panel">
            <section>
                <h3>Disaster Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">Disaster Type</span>
                        <strong>{{ $submission->disaster->type }}</strong>
                    </div>
                    <div class="info-item">
                        <span class="label">Location</span>
                        <strong>
                            {{ $submission->disaster->city ?? 'Unknown' }}, 
                            {{ $submission->disaster->district ?? 'Unknown' }}
                        </strong>
                    </div>
                    <div class="info-item">
                        <span class="label">Disaster Date</span>
                        <strong>{{ $submission->disaster->disaster_date }}</strong>
                    </div>
                    <div class="info-item">
                        <span class="label">Report Type</span>
                        <strong>{{ str_replace('_', ' ', ucfirst($submission->submission_type)) }}</strong>
                    </div>
                </div>
            </section>

            <section class="report-section">
                <h3>Submitted Report</h3>
                <div class="report-content">
                    {!! nl2br(e($submission->description)) !!}
                </div>
            </section>
        </article>

        <aside class="review-sidebar">
            @if ($submission->status === 'pending')
                <div class="action-card">
                    <h4>Review Actions</h4>
                    <form method="POST" action="{{ route('admin.disaster-submissions.update', $submission->id) }}" class="review-form">
                        @csrf
                        @method('PATCH')

                        <div class="form-group">
                            <label for="status" class="form-label">Decision *</label>
                            <select id="status" name="status" class="form-input" required>
                                <option value="">Select action</option>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                            @error('status')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="admin_notes" class="form-label">Admin Notes (Optional)</label>
                            <textarea 
                                id="admin_notes" 
                                name="admin_notes" 
                                class="form-textarea" 
                                rows="6"
                                placeholder="Provide feedback to the volunteer about this submission..."
                            >{{ old('admin_notes') }}</textarea>
                            <small class="form-hint">This will be visible to the volunteer</small>
                            @error('admin_notes')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="btn-submit">Submit Review</button>
                    </form>
                </div>
            @else
                <div class="status-card">
                    <h4>Current Status</h4>
                    <div class="status-details">
                        <p>
                            <strong>Decision:</strong> {{ ucfirst($submission->status) }}
                        </p>
                        @if ($submission->admin_notes)
                            <p>
                                <strong>Admin Notes:</strong>
                            </p>
                            <div class="notes-display">
                                {!! nl2br(e($submission->admin_notes)) !!}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="info-card">
                <h4>Volunteer Information</h4>
                <div class="volunteer-info">
                    <div class="info-item">
                        <span class="label">Name</span>
                        <strong>{{ $submission->person->name }}</strong>
                    </div>
                    <div class="info-item">
                        <span class="label">Email</span>
                        <strong>{{ $submission->person->email ?? 'N/A' }}</strong>
                    </div>
                    <div class="info-item">
                        <span class="label">Phone</span>
                        <strong>{{ $submission->person->phone ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection
