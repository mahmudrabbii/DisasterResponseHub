@extends('public.layout')

@section('title', 'Public Alerts - Disaster Response Hub')
@section('page-title', 'Emergency Alerts')
@section('page-subtitle', 'Latest emergency alerts and warnings')

@section('content')
    <h2>Public Alerts</h2>

    @if ($alerts->count() > 0)
        <div class="alerts-grid">
            @foreach ($alerts as $alert)
                <div class="alert-card">
                    <h4>{{ $alert->title }}</h4>
                    <p>{{ $alert->message }}</p>
                    <span class="alert-time">{{ \Carbon\Carbon::parse($alert->created_at)->format('M d, Y \a\t H:i') }}</span>
                </div>
            @endforeach
        </div>

        <div class="pagination-container">
            {{ $alerts->links() }}
        </div>
    @else
        <div class="empty-state-container">
            <p>No alerts available at the moment.</p>
        </div>
    @endif
@endsection
