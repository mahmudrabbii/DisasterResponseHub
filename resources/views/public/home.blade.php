@extends('public.layout')

@section('title', 'Disaster Response Hub - Home')
@section('page-title', 'Welcome to Disaster Response Hub')
@section('page-subtitle', 'Report disasters, request help, and stay informed')

@section('content')
    <div class="public-hero">
        <h1>Disaster Response Hub</h1>
        <p>Report disasters, request help, and stay informed about emergency situations</p>
    </div>

    <div class="action-cards">
        <a href="{{ route('public.report-disaster') }}" class="action-card-link">
            <div class="panel-card action-card">
                <h3>📢 Report Disaster</h3>
                <p>Report a disaster with location, type, and description. Help us respond faster.</p>
            </div>
        </a>

        <a href="{{ route('public.request-help') }}" class="action-card-link">
            <div class="panel-card action-card">
                <h3>🆘 Request Help</h3>
                <p>Submit requests for aid and support. Our team will review and assist you.</p>
            </div>
        </a>

        <a href="{{ route('public.alerts') }}" class="action-card-link">
            <div class="panel-card action-card">
                <h3>🔔 View Alerts</h3>
                <p>Stay updated with the latest alerts and emergency information.</p>
            </div>
        </a>
    </div>

    @if ($disasters->count() > 0)
        <section class="panel-card full-width">
            <div class="panel-header">
                <h3>Recent Disasters</h3>
                <a href="{{ route('public.disasters') }}">View all</a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Disaster</th>
                        <th>Location</th>
                        <th>Affected</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($disasters as $disaster)
                        <tr>
                            <td>
                                <strong>{{ $disaster->type }}</strong>
                                <div class="muted">{{ \Carbon\Carbon::parse($disaster->disaster_date)->format('M d, Y') }}</div>
                            </td>
                            <td>{{ $disaster->city ?? 'Unknown' }}, {{ $disaster->district ?? 'Unknown' }}</td>
                            <td>{{ number_format($disaster->affected_population ?? 0) }}</td>
                            <td><span class="status-pill status-{{ $disaster->status }}">{{ ucfirst($disaster->status) }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
{{-- 
    @if ($alerts->count() > 0)
        <section class="panel-card full-width recent-disasters-section">
            <div class="panel-header">
                <h3>Recent Alerts</h3>
                <a href="{{ route('public.alerts') }}">View all</a>
            </div>

            <div class="alerts-grid">
                @foreach ($alerts as $alert)
                    <div class="alert-card">
                        <h4>{{ $alert->title }}</h4>
                        <p>{{ $alert->message }}</p>
                        <span class="alert-time">{{ \Carbon\Carbon::parse($alert->created_at)->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
    --}}
@endsection
