@extends('admin.layout')

@section('title', 'Review Disaster Report - DisasterResponseHub')
@section('page-title', 'Disaster Report Detail')

@section('content')
    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>{{ $report->title }}</h3>
            <a href="{{ route('admin.public-disaster-reports') }}">Back to reports</a>
        </div>

        <div class="panel-grid">
            <div class="list-row">
                <strong>Disaster Type</strong>
                <p>{{ $report->disaster_type }}</p>
            </div>

            <div class="list-row">
                <strong>Location</strong>
                <p>{{ $report->city ?? 'Unknown' }}, {{ $report->district ?? 'Unknown' }}</p>
            </div>

            <div class="list-row">
                <strong>Severity</strong>
                <p><span class="status-pill status-{{ $report->severity }}">{{ ucfirst($report->severity) }}</span></p>
            </div>

            <div class="list-row">
                <strong>Current Status</strong>
                <p><span class="status-pill status-{{ $report->status }}">{{ ucfirst(str_replace('_', ' ', $report->status)) }}</span></p>
            </div>

            <div class="list-row">
                <strong>Reported Date</strong>
                <p>{{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y \a\t H:i') }}</p>
            </div>
        </div>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Report Description</h3>
        </div>

        <div class="section-description">
            <p class="description-text">{{ $report->description }}</p>
        </div>
    </section>

    @if ($report->image_path)
        <section class="panel-card full-width">
            <div class="panel-header">
                <h3>Attached Photo/Evidence</h3>
            </div>

            <div class="image-container">
                <img src="{{ asset('storage/' . $report->image_path) }}" alt="Disaster Report Photo">
            </div>
        </section>
    @endif

    <section class="panel-card">
        <div class="panel-header">
            <h3>Update Status</h3>
        </div>

        <form method="POST" action="{{ route('admin.public-disaster-reports.update', $report->id) }}" class="form-grid">
            @csrf
            @method('PATCH')

            <div class="form-group form-wide">
                <label for="status">Report Status</label>
                <select id="status" name="status" required>
                    <option value="reported" @selected($report->status === 'reported')>Reported</option>
                    <option value="in_progress" @selected($report->status === 'in_progress')>In Progress</option>
                    <option value="resolved" @selected($report->status === 'resolved')>Resolved</option>
                </select>
            </div>

            <div class="form-actions form-wide">
                <button type="submit" class="primary-action">Update Status</button>
                <a href="{{ route('admin.public-disaster-reports') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
