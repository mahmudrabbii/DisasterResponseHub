@extends('official.layout')

@section('title', 'Public Help Requests - DisasterResponseHub')
@section('page-title', 'Public Help Requests')
@section('page-subtitle', 'View and approve help requests submitted by the public.')

@section('content')
    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Help Requests</h3>
            <span class="muted">{{ $requests->total() }} requests</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Requester</th>
                    <th>Location</th>
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
                        <td><span class="status-pill status-{{ $request->status }}">{{ ucfirst($request->status) }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y H:i') }}</td>
                        <td>
                            <form method="POST" action="{{ route('official.public-help-requests.update', $request->id) }}" class="inline-form">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="status-select">
                                    <option value="pending" @selected($request->status === 'pending')>Pending</option>
                                    <option value="approved" @selected($request->status === 'approved')>Approve</option>
                                    <option value="rejected" @selected($request->status === 'rejected')>Reject</option>
                                    <option value="completed" @selected($request->status === 'completed')>Completed</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No help requests available.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-container">
            {{ $requests->links() }}
        </div>
    </section>
@endsection
