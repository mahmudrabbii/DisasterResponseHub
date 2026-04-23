@extends('admin.layout')

@section('title', 'Volunteer Submissions - DisasterResponseHub')
@section('page-title', 'Volunteer Disaster Reports')
@section('page-subtitle', 'Review and manage disaster data submitted by volunteers.')

@section('content')
    @if (session('success'))
        <div class="status-banner success">
            {{ session('success') }}
        </div>
    @endif

    <section class="filter-bar">
        <a class="filter-chip {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('admin.disaster-submissions', ['status' => 'all']) }}">All</a>
        <a class="filter-chip {{ $filter === 'pending' ? 'active' : '' }}" href="{{ route('admin.disaster-submissions', ['status' => 'pending']) }}">Pending Review</a>
        <a class="filter-chip {{ $filter === 'approved' ? 'active' : '' }}" href="{{ route('admin.disaster-submissions', ['status' => 'approved']) }}">Approved</a>
        <a class="filter-chip {{ $filter === 'rejected' ? 'active' : '' }}" href="{{ route('admin.disaster-submissions', ['status' => 'rejected']) }}">Rejected</a>
    </section>

    <section class="submissions-table-section">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Volunteer</th>
                        <th>Disaster</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($submissions as $submission)
                        <tr>
                            <td class="title-cell">
                                <strong>{{ $submission->title }}</strong>
                            </td>
                            <td>
                                {{ $submission->person->name ?? 'Unknown' }}
                            </td>
                            <td>
                                {{ $submission->disaster->type }}<br>
                                <small>{{ $submission->disaster->city }}, {{ $submission->disaster->district }}</small>
                            </td>
                            <td>
                                <span class="type-badge">{{ str_replace('_', ' ', ucfirst($submission->submission_type)) }}</span>
                            </td>
                            <td>
                                <span class="status-pill status-{{ $submission->status }}">
                                    {{ ucfirst($submission->status) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $submission->created_at->format('M d, Y') }}</small>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('admin.disaster-submissions.show', $submission->id) }}" class="action-link">Review</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-cell">
                                <p class="empty-state">No submissions found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
