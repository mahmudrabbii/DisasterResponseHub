@extends('official.layout')

@section('title', 'Review Disaster Report - DisasterResponseHub')
@section('page-title', 'Disaster Report Detail')

@section('content')
    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>{{ $report->title }}</h3>
            <a href="{{ route('official.public-disaster-reports') }}">Back to reports</a>
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
@endsection
