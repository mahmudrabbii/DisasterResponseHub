@extends('admin.layout')

@section('title', 'Public Help Requests - DisasterResponseHub')
@section('page-title', 'Public Help Requests')
@section('page-subtitle', 'View and manage help requests submitted by the public.')

@section('content')
    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Help Requests</h3>
            <span class="muted">{{ $requests->total() }} total requests</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Requester</th>
                    <th>Location</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($requests as $request)
                    <tr>
                        <td>
                            <strong>{{ $request->name }}</strong>
                            <div class="muted">{{ $request->email }}</div>
                        </td>
                        <td>{{ $request->city ?? 'Unknown' }}, {{ $request->district ?? 'Unknown' }}</td>
                        <td>
                            @if ($request->phone)
                                <small>{{ $request->phone }}</small>
                            @else
                                <span class="muted">No phone</span>
                            @endif
                        </td>
                        <td><span class="status-pill status-{{ $request->status }}">{{ ucfirst($request->status) }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.public-help-requests.show', $request->id) }}" class="action-link">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No help requests found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            {{ $requests->links() }}
        </div>
    </section>
@endsection
