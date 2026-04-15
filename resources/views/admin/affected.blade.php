@extends('admin.layout')

@section('title', 'Affected People - DisasterResponseHub')
@section('page-title', 'Affected People List')
@section('page-subtitle', 'Register and update affected residents tied to disasters.')

@section('content')
    @php
        $editingAffected = null;
        if (request()->filled('edit')) {
            $editingAffected = $affectedPeople->firstWhere('beneficiary_id', (int) request('edit'));
        }
    @endphp

    <section class="panel-card">
        <div class="panel-header">
            <h3>{{ $editingAffected ? 'Edit affected person' : 'Add affected person' }}</h3>
            @if ($editingAffected)
                <a href="{{ route('admin.affected-people') }}">Cancel edit</a>
            @endif
        </div>

        <form method="POST" action="{{ $editingAffected ? route('admin.affected-people.update', $editingAffected->beneficiary_id) : route('admin.affected-people.store') }}" class="form-grid">
            @csrf
            @if ($editingAffected)
                @method('PATCH')
            @endif

            <div class="form-group">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $editingAffected->name ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="location_id">Location</label>
                <select id="location_id" name="location_id" required>
                    <option value="">Select location</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" {{ (string) old('location_id', $editingAffected->location_id ?? '') === (string) $location->id ? 'selected' : '' }}>
                            {{ $location->city }}, {{ $location->district }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="disaster_id">Disaster</label>
                <select id="disaster_id" name="disaster_id" required>
                    <option value="">Select disaster</option>
                    @foreach ($disasters as $disaster)
                        <option value="{{ $disaster->id }}" {{ (string) old('disaster_id', $editingAffected->disaster_id ?? '') === (string) $disaster->id ? 'selected' : '' }}>
                            {{ $disaster->type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="family_size">Family size</label>
                <input id="family_size" name="family_size" type="number" min="1" value="{{ old('family_size', $editingAffected->family_size ?? 1) }}">
            </div>

            <div class="form-group form-wide">
                <label for="aid_received">Aid received</label>
                <input id="aid_received" name="aid_received" type="text" value="{{ old('aid_received', $editingAffected->aid_received ?? '') }}" placeholder="Food package, medical kit, water supply">
            </div>

            <div class="form-actions form-wide">
                <button type="submit" class="primary-action">{{ $editingAffected ? 'Update affected person' : 'Create affected person' }}</button>
            </div>
        </form>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Affected people list</h3>
            <span class="muted">{{ $affectedPeople->count() }} records</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Disaster</th>
                    <th>Aid received</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($affectedPeople as $person)
                    <tr>
                        <td>{{ $person->name }}</td>
                        <td>{{ $person->city ?? 'N/A' }}, {{ $person->district ?? 'N/A' }}</td>
                        <td>{{ $person->disaster_name ?? 'N/A' }}</td>
                        <td>{{ $person->aid_received ?? 'N/A' }}</td>
                        <td>{{ $person->created_at }}</td>
                        <td class="actions-cell">
                            <a class="action-link" href="{{ route('admin.affected-people', ['edit' => $person->beneficiary_id]) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.affected-people.destroy', $person->beneficiary_id) }}" class="inline-form" onsubmit="return confirm('Delete this affected person?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="danger-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No affected people found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection