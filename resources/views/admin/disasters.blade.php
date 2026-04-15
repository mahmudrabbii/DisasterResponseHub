@extends('admin.layout')

@section('title', 'Disaster Management - DisasterResponseHub')
@section('page-title', 'Disaster Management')
@section('page-subtitle', 'Register, update, view, and delete disaster records.')

@section('content')
    @php
        $editingDisaster = null;
        if (request()->filled('edit')) {
            $editingDisaster = $disasters->firstWhere('id', (int) request('edit'));
        }
    @endphp

    <section class="panel-card">
        <div class="panel-header">
            <h3>{{ $editingDisaster ? 'Edit disaster' : 'Add disaster' }}</h3>
            @if ($editingDisaster)
                <a href="{{ route('admin.disasters') }}">Cancel edit</a>
            @endif
        </div>

        <form method="POST" action="{{ $editingDisaster ? route('admin.disasters.update', $editingDisaster->id) : route('admin.disasters.store') }}" class="form-grid">
            @csrf
            @if ($editingDisaster)
                @method('PATCH')
            @endif

            <div class="form-group">
                <label for="type">Disaster name / type</label>
                <input id="type" name="type" type="text" value="{{ old('type', $editingDisaster->type ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input id="city" name="city" type="text" value="{{ old('city', $editingDisaster->city ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="district">District</label>
                <input id="district" name="district" type="text" value="{{ old('district', $editingDisaster->district ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="country">Country</label>
                <input id="country" name="country" type="text" value="{{ old('country', $editingDisaster->country ?? 'Bangladesh') }}">
            </div>

            <div class="form-group">
                <label for="disaster_date">Date</label>
                <input id="disaster_date" name="disaster_date" type="date" value="{{ old('disaster_date', $editingDisaster->disaster_date ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="affected_population">Affected population</label>
                <input id="affected_population" name="affected_population" type="number" min="0" value="{{ old('affected_population', $editingDisaster->affected_population ?? 0) }}" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                @php($statusValue = old('status', $editingDisaster->status ?? 'pending'))
                <select id="status" name="status" required>
                    <option value="pending" {{ $statusValue === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ $statusValue === 'in_progress' ? 'selected' : '' }}>Active</option>
                    <option value="resolved" {{ $statusValue === 'resolved' ? 'selected' : '' }}>Resolved</option>
                </select>
            </div>

            <div class="form-actions form-wide">
                <button type="submit" class="primary-action">{{ $editingDisaster ? 'Update disaster' : 'Create disaster' }}</button>
            </div>
        </form>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>All disasters</h3>
            <span class="muted">{{ $disasters->count() }} records</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Name / Type</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Affected</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($disasters as $disaster)
                    <tr>
                        <td>{{ $disaster->type }}</td>
                        <td>{{ $disaster->city ?? 'N/A' }}, {{ $disaster->district ?? 'N/A' }}</td>
                        <td>{{ $disaster->disaster_date }}</td>
                        <td><span class="status-pill status-{{ $disaster->status }}">{{ $disaster->status }}</span></td>
                        <td>{{ number_format($disaster->affected_population) }}</td>
                        <td class="actions-cell">
                            <a class="action-link" href="{{ route('admin.disasters', ['edit' => $disaster->id]) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.disasters.destroy', $disaster->id) }}" class="inline-form" onsubmit="return confirm('Delete this disaster and linked records?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="danger-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No disasters found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection