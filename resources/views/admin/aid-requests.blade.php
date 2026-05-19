@extends('admin.layout')

@section('title', 'Aid Requests - Admin Dashboard')

@section('content')
    @if ($errors->any())
        <div class="error-banner">
            <strong>Validation errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="status-banner">{{ session('status') }}</div>
    @endif

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Aid Requests</h3>
            <span class="muted">{{ $aidRequests->count() }} requests</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Requester</th>
                    <th>Aid Type</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($aidRequests as $aidRequest)
                    <tr>
                        <td>
                            <strong>{{ $aidRequest->person_name ?? 'Unknown' }}</strong>
                            <div class="muted">{{ $aidRequest->person_email ?? 'N/A' }}</div>
                        </td>
                        <td>{{ $aidRequest->aid_type ?? 'N/A' }}</td>
                        <td>{{ $aidRequest->city ?? 'Unknown' }}, {{ $aidRequest->district ?? 'Unknown' }}</td>
                        <td><span class="status-pill status-{{ $aidRequest->status }}">{{ ucfirst($aidRequest->status) }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($aidRequest->created_at)->format('M d, Y H:i') }}</td>
                        <td>
                            <div class="actions-cell">
                                <form method="POST" action="{{ route('admin.aid-requests.update', $aidRequest->id) }}" class="inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="status-select">
                                        <option value="pending" @selected('pending')>Pending</option>
                                        <option value="approved" @selected('approved')>Approve</option>
                                        <option value="rejected" @selected('rejected')>Reject</option>
                                        <option value="completed" @selected('completed')>Completed</option>
                                    </select>
                                </form>
                                <form method="POST" action="{{ route('admin.aid-requests.destroy', $aidRequest->id) }}" class="inline-delete" onsubmit="return confirm('Are you sure you want to delete this aid request?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger-small">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No aid requests found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
