@extends('admin.layout')

@section('title', 'Aid Requests - Admin Dashboard')

@section('content')
    <div class="admin-page">
        <h2>Aid Requests Management</h2>
        <p class="section-subtitle">Create, track, and manage aid requests from affected persons</p>

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

        <div class="form-section">
            <h3>{{ request('edit') ? 'Edit Aid Request Status' : 'Create New Aid Request' }}</h3>

            @if (request('edit'))
                <form method="POST" action="{{ route('admin.aid-requests.update', request('edit')) }}" class="admin-form">
                    @csrf
                    @method('PATCH')

                    <div class="form-row">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ old('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ old('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="primary-action">Update Status</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.aid-requests.store') }}" class="admin-form">
                    @csrf

                    <div class="form-row">
                        <div class="form-group">
                            <label for="person_id">Aid Requested By</label>
                            <select id="person_id" name="person_id" required>
                                <option value="">Select person</option>
                                @foreach ($people as $person)
                                    <option value="{{ $person->id }}" {{ (string) old('person_id') === (string) $person->id ? 'selected' : '' }}>
                                        {{ $person->name }} ({{ $person->email ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="location_id">Location</label>
                            <select id="location_id" name="location_id" required>
                                <option value="">Select location</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" {{ (string) old('location_id') === (string) $location->id ? 'selected' : '' }}>
                                        {{ $location->city }}, {{ $location->district }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="aid_type_id">Aid Type</label>
                            <select id="aid_type_id" name="aid_type_id" required>
                                <option value="">Select aid type</option>
                                @foreach ($aidTypes as $aidType)
                                    <option value="{{ $aidType->id }}" {{ (string) old('aid_type_id') === (string) $aidType->id ? 'selected' : '' }}>
                                        {{ $aidType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4" required placeholder="Describe the aid request in detail...">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="primary-action">Create Aid Request</button>
                </form>
            @endif
        </div>

        <div class="table-section">
            <h3>Aid Requests List</h3>
            @if ($aidRequests->count() > 0)
                <div class="admin-table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Person</th>
                                <th>Aid Type</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($aidRequests as $aidRequest)
                                <tr>
                                    <td>{{ $aidRequest->person_name ?? 'Unknown' }}</td>
                                    <td>{{ $aidRequest->aid_type ?? 'N/A' }}</td>
                                    <td>{{ $aidRequest->city ?? 'N/A' }}, {{ $aidRequest->district ?? 'N/A' }}</td>
                                    <td>
                                        <span class="status-pill status-{{ $aidRequest->status }}">
                                            {{ ucfirst($aidRequest->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $aidRequest->created_at }}</td>
                                    <td class="action-buttons">
                                        <a href="?edit={{ $aidRequest->id }}" class="btn-secondary">Edit</a>
                                        <form method="POST" action="{{ route('admin.aid-requests.destroy', $aidRequest->id) }}" class="inline-delete" onsubmit="return confirm('Delete this aid request?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="empty-state">No aid requests found. Create one to get started.</p>
            @endif
        </div>
    </div>
@endsection
