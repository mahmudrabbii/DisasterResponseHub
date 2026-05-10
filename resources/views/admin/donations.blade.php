@extends('admin.layout')

@section('title', 'Manage Donations - DisasterResponseHub')
@section('page-title', 'Donation Management')
@section('page-subtitle', 'Create and manage fundraising campaigns.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-donations.css') }}">
@endpush

@section('content')
    @php
        $editingDonation = null;
        if (request()->filled('edit')) {
            $editingDonation = $donations->firstWhere('id', (int) request('edit'));
        }
    @endphp

    <section class="panel-card">
        <div class="panel-header">
            <h3>{{ $editingDonation ? 'Edit Campaign' : 'Create New Campaign' }}</h3>
            @if ($editingDonation)
                <a href="{{ route('admin.donations') }}">Cancel edit</a>
            @endif
        </div>

        <form method="POST" action="{{ $editingDonation ? route('admin.donations.update', $editingDonation->id) : route('admin.donations.store') }}" class="form-grid">
            @csrf
            @if ($editingDonation)
                @method('PATCH')
            @endif

            <div class="form-group">
                <label for="disaster_id">Disaster <span class="required">*</span></label>
                <select id="disaster_id" name="disaster_id" required @if($editingDonation) disabled @endif>
                    <option value="">Select Disaster</option>
                    @foreach ($disasters as $disaster)
                        <option value="{{ $disaster->id }}" 
                            @selected(old('disaster_id', $editingDonation->disaster_id ?? ''))
                        >{{ $disaster->type }} - {{ $disaster->disaster_date ?? 'Ongoing' }}</option>
                    @endforeach
                </select>
                @if ($editingDonation)
                    <input type="hidden" name="disaster_id" value="{{ $editingDonation->disaster_id }}">
                @endif
                <small>Select the disaster this campaign will support</small>
            </div>

            <div class="form-group">
                <label for="title">Campaign Title <span class="required">*</span></label>
                <input id="title" name="title" type="text" placeholder="e.g., Emergency Food & Water Relief" value="{{ old('title', $editingDonation->title ?? '') }}" required>
                <small>Give your campaign a clear, compelling name</small>
            </div>

            <div class="form-group">
                <label for="amount">Target Amount (৳) <span class="required">*</span></label>
                <input id="amount" name="amount" type="number" step="1" min="1" placeholder="e.g., 100000" value="{{ old('amount', $editingDonation->amount ?? '') }}" required>
                <small>Set a fundraising goal for this campaign</small>
            </div>

            <!-- Hidden field for campaign role -->
            <input type="hidden" name="role" value="organizer">

            <div class="form-group">
                <label for="status">Status <span class="required">*</span></label>
                <select id="status" name="status" required>
                    <option value="">Select Status</option>
                    <option value="active" @selected(old('status', $editingDonation->status ?? '') === 'active')>Active (Accepting Donations)</option>
                    <option value="completed" @selected(old('status', $editingDonation->status ?? '') === 'completed')>Completed (Closed)</option>
                </select>
                <small>Active campaigns accept public donations</small>
            </div>

            <div class="form-actions form-wide">
                <button type="submit" class="primary-action">{{ $editingDonation ? 'Update Campaign' : 'Create Campaign' }}</button>
            </div>
        </form>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Active Campaigns</h3>
            <span class="muted">{{ $donations->count() }} campaign(s)</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Campaign Title</th>
                    <th>Disaster</th>
                    <th>Target Amount (৳)</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($donations as $donation)
                    <tr>
                        <td><strong>{{ $donation->title }}</strong></td>
                        <td>{{ $donation->disaster_type ?? 'N/A' }}</td>
                        <td>৳{{ number_format($donation->amount, 0) }}</td>
                        <td><span class="status-pill status-{{ $donation->status }}">{{ ucfirst($donation->status) }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($donation->created_at)->format('M d, Y') }}</td>
                        <td class="actions-cell">
                            <a class="action-link" href="{{ route('admin.donations', ['edit' => $donation->id]) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.donations.destroy', $donation->id) }}" class="inline-form" onsubmit="return confirm('Delete this campaign? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="danger-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No campaigns created yet. <a href="{{ route('admin.donations') }}">Create your first campaign</a></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
