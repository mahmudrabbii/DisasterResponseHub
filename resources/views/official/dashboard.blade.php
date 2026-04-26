@extends('official.layout')

@section('title', 'NGO Official Dashboard - DisasterResponseHub')
@section('page-title', 'NGO Official Dashboard')
@section('page-subtitle', 'Use the sidebar to move between disaster, volunteer, resource, support, and policy pages.')

@section('content')
    <section class="metrics-grid">
        <article class="metric-card">
            <span>Active disasters</span>
            <strong>{{ $stats['approved_disasters'] }}</strong>
            <small>Disasters currently in response mode or resolved</small>
        </article>
        <article class="metric-card">
            <span>Assigned volunteers</span>
            <strong>{{ $stats['assigned_volunteers'] }}</strong>
            <small>Tracked volunteer assignments</small>
        </article>
        <article class="metric-card">
            <span>Resource requests</span>
            <strong>{{ $stats['pending_resource_requests'] }}</strong>
            <small>Outstanding requests awaiting action</small>
        </article>
        <article class="metric-card">
            <span>Community supports</span>
            <strong>{{ $stats['pending_supports'] }}</strong>
            <small>Beneficiary cases awaiting approval</small>
        </article>
    </section>

    <section class="panel-grid">
        <!--
        <article class="panel-card">
            <div class="panel-header">
                <h3>Quick actions</h3>
                <span class="muted">Jump directly into official tasks</span>
            </div>

            <div class="list-row">
                <div>
                    <strong>Disaster handling</strong>
                    <p>Review approved disasters and update their status.</p>
                </div>
                <a class="action-link" href="{{ route('official.disasters') }}">Open module</a>
            </div>
            <div class="list-row">
                <div>
                    <strong>Volunteer coordination</strong>
                    <p>Assign volunteers and monitor work hours.</p>
                </div>
                <a class="action-link" href="{{ route('official.volunteers') }}">Open module</a>
            </div>
            <div class="list-row">
                <div>
                    <strong>Resource handling</strong>
                    <p>Request supplies and log usage.</p>
                </div>
                <a class="action-link" href="{{ route('official.resources') }}">Open module</a>
            </div>
            <div class="list-row">
                <div>
                    <strong>Community support</strong>
                    <p>Approve support records for affected people.</p>
                </div>
                <a class="action-link" href="{{ route('official.community-supports') }}">Open module</a>
            </div>
            <div class="list-row">
                <div>
                    <strong>Policies</strong>
                    <p>Publish guidance and send alerts to volunteers.</p>
                </div>
                <a class="action-link" href="{{ route('official.policies') }}">Open module</a>
            </div>
        </article>
    -->

        <article class="panel-card">
            <div class="panel-header">
                <h3>Recent policies</h3>
                <span class="muted">Latest guidance published for response teams</span>
            </div>

            @forelse ($policies as $policy)
                <div class="list-row">
                    <div>
                        <strong>{{ $policy->title }}</strong>
                        <p>{{ $policy->description }}</p>
                    </div>
                </div>
            @empty
                <p class="empty-state">No policies available.</p>
            @endforelse
        </article>
    </section>
@endsection