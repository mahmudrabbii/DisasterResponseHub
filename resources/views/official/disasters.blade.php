@extends('official.layout')

@section('title', 'Disaster Handling - DisasterResponseHub')
@section('page-title', 'Disaster Handling')
@section('page-subtitle', 'View approved disasters and update their response status.')

@section('content')
    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Approved disasters</h3>
            <span class="muted">Disaster operations only</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Disaster</th>
                    <th>Location</th>
                    <th>Affected</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($approvedDisasters as $disaster)
                    <tr>
                        <td>
                            <strong>{{ $disaster->type }}</strong>
                            <div class="muted">{{ $disaster->country ?? 'Unknown' }}</div>
                        </td>
                        <td>{{ $disaster->city ?? 'Unknown' }}, {{ $disaster->district ?? 'Unknown' }}</td>
                        <td>{{ number_format($disaster->affected_population ?? 0) }}</td>
                        <td>{{ \Carbon\Carbon::parse($disaster->disaster_date)->format('M d, Y') }}</td>
                        <td><span class="status-pill status-{{ $disaster->status }}">{{ $disaster->status }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('official.disasters.update-status', $disaster->id) }}" class="inline-form">
                                @csrf
                                @method('PATCH')
                                <select name="status">
                                    <option value="pending" @selected('pending')>Pending</option>
                                    <option value="in_progress" @selected('in_progress')>In progress</option>
                                    <option value="resolved" @selected('resolved')>Resolved</option>
                                </select>
                                <button type="submit" class="primary-action">Update</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No approved disasters are available yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection