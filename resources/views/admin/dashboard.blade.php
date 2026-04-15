@extends('admin.layout')

@section('title', 'Admin Dashboard - DisasterResponseHub')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Overview of users, disasters, volunteers, resources, donations, and affected people.')

@section('content')
    <section class="metrics-grid">
        <article class="metric-card">
            <span>Total users</span>
            <strong>{{ $stats['total_users'] }}</strong>
            <small>All registered accounts</small>
        </article>
        <article class="metric-card">
            <span>Total disasters</span>
            <strong>{{ $stats['total_disasters'] }}</strong>
            <small>Registered disaster records</small>
        </article>
        <article class="metric-card">
            <span>Total volunteers</span>
            <strong>{{ $stats['total_volunteers'] }}</strong>
            <small>Volunteer accounts and profiles</small>
        </article>
        <article class="metric-card">
            <span>Total resources</span>
            <strong>{{ $stats['total_resources'] }}</strong>
            <small>Units in stock</small>
        </article>
        <article class="metric-card">
            <span>Total donations</span>
            <strong>{{ number_format($stats['total_donations'], 2) }}</strong>
            <small>Sum of fundraising records</small>
        </article>
        <article class="metric-card">
            <span>Affected people</span>
            <strong>{{ $stats['total_affected_people'] }}</strong>
            <small>Registered beneficiary records</small>
        </article>
    </section>

    <section class="panel-grid">
        <article class="panel-card">
            <div class="panel-header">
                <h3>Recent users</h3>
                <a href="{{ route('admin.users') }}">Open user management</a>
            </div>

            @forelse ($recentUsers as $user)
                <div class="list-row">
                    <div>
                        <strong>{{ $user->name }}</strong>
                        <p>{{ $user->email }}</p>
                    </div>
                    <span class="status-pill status-{{ $user->role }}">{{ $user->role }}</span>
                </div>
            @empty
                <p class="empty-state">No users found.</p>
            @endforelse
        </article>

        <article class="panel-card">
            <div class="panel-header">
                <h3>Recent disasters</h3>
                <a href="{{ route('admin.disasters') }}">Open disaster management</a>
            </div>

            @forelse ($recentDisasters as $disaster)
                <div class="list-row">
                    <div>
                        <strong>{{ $disaster->type }}</strong>
                        <p>{{ $disaster->city ?? 'Unknown city' }}, {{ $disaster->district ?? 'Unknown district' }}</p>
                    </div>
                    <span class="status-pill status-{{ $disaster->status }}">{{ $disaster->status }}</span>
                </div>
            @empty
                <p class="empty-state">No disasters recorded yet.</p>
            @endforelse
        </article>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Recent resource stock</h3>
            <a href="{{ route('admin.resources') }}">Open inventory</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Expiry</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($recentResources as $resource)
                    <tr>
                        <td>{{ $resource->name }}</td>
                        <td>{{ $resource->category }}</td>
                        <td>{{ $resource->quantity }}</td>
                        <td>{{ $resource->expiry_date ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-state">No inventory recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection