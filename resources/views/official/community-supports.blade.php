@extends('official.layout')

@section('title', 'Community Support - DisasterResponseHub')
@section('page-title', 'Community Support')
@section('page-subtitle', 'Add and approve affected people support records.')

@section('content')
    @php
        $editingSupport = null;
        if (request()->filled('edit')) {
            $editingSupport = $communitySupports->firstWhere('beneficiary_id', (int) request('edit'));
        }
    @endphp

    <section class="panel-grid">
        <article class="panel-card">
            <div class="panel-header">
                <h3>{{ $editingSupport ? 'Edit support record' : 'Add support record' }}</h3>
                @if ($editingSupport)
                    <a href="{{ route('official.community-supports') }}">Cancel edit</a>
                @endif
            </div>

            <form method="POST" action="{{ $editingSupport ? route('official.community-supports.update', $editingSupport->beneficiary_id) : route('official.community-supports.store') }}" class="stack-form">
                @csrf
                @if ($editingSupport)
                    @method('PATCH')
                @endif
                <div class="form-group">
                    <label for="name">Affected person</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $editingSupport->name ?? '') }}" required>
                </div>
                <div class="form-group">
                    <label for="location_id">Location</label>
                    <select id="location_id" name="location_id" required>
                        <option value="">Select location</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected(old('location_id', $editingSupport->location_id ?? null) == $location->id)>{{ $location->city }}, {{ $location->district }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="disaster_id">Disaster</label>
                    <select id="disaster_id" name="disaster_id" required>
                        <option value="">Select disaster</option>
                        @foreach ($disasters as $disaster)
                            <option value="{{ $disaster->id }}" @selected(old('disaster_id', $editingSupport->disaster_id ?? null) == $disaster->id)>{{ $disaster->type }} - {{ $disaster->city ?? 'Unknown city' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="family_size">Family size</label>
                    <input id="family_size" name="family_size" type="number" min="1" value="{{ old('family_size', $editingSupport->family_size ?? 1) }}">
                </div>
                <div class="form-group">
                    <label for="aid_received">Aid received</label>
                    <input id="aid_received" name="aid_received" type="text" value="{{ old('aid_received', $editingSupport->aid_received ?? '') }}">
                </div>
                <div class="form-group">
                    <label for="support_status">Approval</label>
                    <select id="support_status" name="support_status" required>
                        <option value="pending" @selected(old('pending')>Pending</option>
                        <option value="approved" @selected(old('approved')>Approved</option>
                        <option value="rejected" @selected(old('rejected')>Rejected</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="support_notes">Notes</label>
                    <textarea id="support_notes" name="support_notes" rows="3">{{ old('support_notes', $editingSupport->support_notes ?? '') }}</textarea>
                </div>
                <button type="submit" class="primary-action">{{ $editingSupport ? 'Update support record' : 'Save support record' }}</button>
            </form>
        </article>

        <article class="panel-card">
            <div class="panel-header">
                <h3>Support list</h3>
                <span class="muted">Approved and pending entries</span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Disaster</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($communitySupports as $support)
                        <tr>
                            <td>{{ $support->name }}</td>
                            <td>{{ $support->disaster_type }}</td>
                            <td>{{ $support->city ?? 'N/A' }}, {{ $support->district ?? 'N/A' }}</td>
                            <td><span class="status-pill status-{{ $support->support_status }}">{{ ucfirst(str_replace('_', ' ', $support->support_status)) }}</span></td>
                            <td>
                                <a href="{{ route('official.community-supports') }}?edit={{ $support->beneficiary_id }}" class="action-link">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">No community support records found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection