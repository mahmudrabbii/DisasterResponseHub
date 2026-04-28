@extends('public.layout')

@section('title', 'Active Disasters - Disaster Response Hub')@section('page-title', 'Active Disasters')
@section('page-subtitle', 'View all active disasters and their details')
@section('content')
    <h2>Active Disasters</h2>

    @if ($disasters->count() > 0)
        <section class="panel-card full-width">
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Disaster</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Affected</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($disasters as $disaster)
                        <tr>
                            <td>
                                <strong>{{ $disaster->type }}</strong>
                                <div class="muted">{{ $disaster->country ?? 'Bangladesh' }}</div>
                            </td>
                            <td>{{ $disaster->city ?? 'Unknown' }}, {{ $disaster->district ?? 'Unknown' }}</td>
                            <td>{{ \Carbon\Carbon::parse($disaster->disaster_date)->format('M d, Y') }}</td>
                            <td>{{ number_format($disaster->affected_population ?? 0) }}</td>
                            <td><span class="status-pill status-{{ $disaster->status }}">{{ ucfirst($disaster->status) }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="pagination-container">
            {{ $disasters->links() }}
        </div>
    @else
        <div class="empty-state-container">
            <p>No active disasters at the moment.</p>
        </div>
    @endif
@endsection
