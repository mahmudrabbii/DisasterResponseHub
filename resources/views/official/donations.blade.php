@extends('official.layout')

@section('title', 'Manage Donations - DisasterResponseHub')
@section('page-title', 'Donation Management')
@section('page-subtitle', 'Create, track, and manage fundraising campaigns and donor contributions.')

@section('content')
    @php
        $editingDonation = null;
        if (request()->filled('edit')) {
            $editingDonation = $donations->firstWhere('id', (int) request('edit'));
        }
    @endphp

    <section class="panel-card">
        <div class="panel-header">
            <h3>{{ $editingDonation ? 'Edit donation record' : 'Add new donation' }}</h3>
            @if ($editingDonation)
                <a href="{{ route('official.donations') }}">Cancel edit</a>
            @endif
        </div>

        <form method="POST" action="{{ $editingDonation ? route('official.donations.update', $editingDonation->id) : route('official.donations.store') }}" class="form-grid">
            @csrf
            @if ($editingDonation)
                @method('PATCH')
            @endif

            <div class="form-group">
                <label for="person_id">Donor/Organizer Name</label>
                <select id="person_id" name="person_id" required @if($editingDonation) disabled @endif>
                    <option value="">Select Person</option>
                    @foreach ($people as $person)
                        <option value="{{ $person->id }}" 
                            @selected(old('person_id')
                        >{{ $person->name }}</option>
                    @endforeach
                </select>
                @if ($editingDonation)
                    <input type="hidden" name="person_id" value="{{ $editingDonation->person_id }}">
                @endif
            </div>

            <div class="form-group">
                <label for="disaster_id">Associated Disaster</label>
                <select id="disaster_id" name="disaster_id" required @if($editingDonation) disabled @endif>
                    <option value="">Select Disaster</option>
                    @foreach ($disasters as $disaster)
                        <option value="{{ $disaster->id }}" 
                            @selected(old('disaster_id')
                        >{{ $disaster->type }}</option>
                    @endforeach
                </select>
                @if ($editingDonation)
                    <input type="hidden" name="disaster_id" value="{{ $editingDonation->disaster_id }}">
                @endif
            </div>

            <div class="form-group">
                <label for="title">Campaign/Donation Title</label>
                <input id="title" name="title" type="text" value="{{ old('title', $editingDonation->title ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="amount">Amount (৳)</label>
                <input id="amount" name="amount" type="number" step="0.01" min="0" value="{{ old('amount', $editingDonation->amount ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="role">Role Type</label>
                <select id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="donor" @selected(old('role')>Donor</option>
                    <option value="organizer" @selected(old('role')>Campaign Organizer</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="">Select Status</option>
                    <option value="active" @selected(old('status')>Active</option>
                    <option value="completed" @selected(old('status')>Completed</option>
                </select>
            </div>

            <div class="form-actions form-wide">
                <button type="submit" class="primary-action">{{ $editingDonation ? 'Update donation' : 'Create donation' }}</button>
            </div>
        </form>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>All donations</h3>
            <span class="muted">{{ $donations->count() }} records</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Donor/Organizer</th>
                    <th>Disaster</th>
                    <th>Amount (৳)</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($donations as $donation)
                    <tr>
                        <td>{{ $donation->title }}</td>
                        <td>{{ $donation->person_name ?? 'N/A' }}</td>
                        <td>{{ $donation->disaster_type ?? 'N/A' }}</td>
                        <td>{{ number_format($donation->amount, 2) }}</td>
                        <td><span class="status-pill status-{{ $donation->role }}">{{ $donation->role }}</span></td>
                        <td><span class="status-pill status-{{ $donation->status }}">{{ ucfirst(str_replace('_', ' ', $donation->status)) }}</span></td>
                        <td>{{ $donation->created_at }}</td>
                        <td class="actions-cell">
                            <a class="action-link" href="{{ route('official.donations', ['edit' => $donation->id]) }}">Edit</a>
                            <form method="POST" action="{{ route('official.donations.destroy', $donation->id) }}" class="inline-form" onsubmit="return confirm('Delete this donation record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="danger-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state">No donation records found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
