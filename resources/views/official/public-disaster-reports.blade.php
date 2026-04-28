@extends('official.layout')

@section('title', 'Public Disaster Reports - DisasterResponseHub')
@section('page-title', 'Public Disaster Reports')
@section('page-subtitle', 'View disaster reports submitted by the public.')

@section('content')
    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Disaster Reports</h3>
            <span class="muted">{{ $reports->total() }} reports</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Disaster Type</th>
                    <th>Location</th>
                    <th>Severity</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($reports as $report)
                    <tr>
                        <td><strong>{{ $report->title }}</strong></td>
                        <td>{{ $report->disaster_type }}</td>
                        <td>{{ $report->city ?? 'Unknown' }}, {{ $report->district ?? 'Unknown' }}</td>
                        <td>
                            <span class="status-pill status-{{ $report->severity }}">{{ ucfirst($report->severity) }}</span>
                        </td>
                        <td><span class="status-pill status-{{ $report->status }}">{{ ucfirst(str_replace('_', ' ', $report->status)) }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y H:i') }}</td>
                        <td><a href="{{ route('official.public-disaster-reports.show', $report->id) }}" class="link-primary">View Details</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">No disaster reports available.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            {{ $reports->links() }}
        </div>
    </section>
@endsection
