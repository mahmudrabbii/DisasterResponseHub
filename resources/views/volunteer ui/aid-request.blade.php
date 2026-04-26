@extends('volunteer ui.layout')

@section('title', 'Aid Request - DisasterResponseHub')
@section('page-title', 'Aid Request')
@section('page-subtitle', 'Create and track aid requests for the disasters you are currently supporting.')

@section('content')
    <section class="panel-grid profile-grid">
        <article class="panel-card">
            <h3>Submit a new request</h3>

            <form method="POST" action="{{ route('volunteer.aid-requests.store') }}" class="stack-form">
                @csrf

                <label for="disaster_id">Disaster</label>
                <select id="disaster_id" name="disaster_id" required>
                    <option value="">Select an assigned disaster</option>
                    @foreach ($assignedDisasters as $disaster)
                        <option value="{{ $disaster->id }}" {{ old('disaster_id') == $disaster->id ? 'selected' : '' }}>
                            {{ $disaster->type }} - {{ $disaster->city ?? 'Unknown city' }}, {{ $disaster->district ?? 'Unknown district' }} ({{ !empty($disaster->disaster_date) ? \Illuminate\Support\Carbon::parse($disaster->disaster_date)->format('M d, Y') : 'No date' }})
                        </option>
                    @endforeach
                </select>

                <label>Aid types</label>
                <div class="checkbox-list" role="group" aria-label="Aid types">
                    @foreach ($aidTypes as $aidType)
                        <label class="checkbox-item" for="aid_type_{{ $aidType->id }}">
                            <input
                                id="aid_type_{{ $aidType->id }}"
                                type="checkbox"
                                name="aid_type_ids[]"
                                value="{{ $aidType->id }}"
                                {{ in_array($aidType->id, old('aid_type_ids', [])) ? 'checked' : '' }}
                            >
                            <span>{{ $aidType->name }}</span>
                        </label>
                    @endforeach
                </div>
                <small class="muted">Select one or more aid types.</small>

                <label for="description">Description</label>
                <textarea id="description" name="description" rows="6" required>{{ old('description') }}</textarea>

                <button type="submit" class="primary-action">Send request</button>
            </form>
        </article>

        <article class="panel-card">
            <div class="panel-header">
                <h3>My requests</h3>
                <span class="muted">{{ $myAidRequests->count() }} total</span>
            </div>

            @forelse ($myAidRequests as $request)
                <div class="request-card">
                    <div class="panel-header compact">
                        <div>
                            <strong>{{ $request->aid_type ?? 'Aid request' }}</strong>
                            <p>{{ $request->city ?? 'Unknown city' }}, {{ $request->district ?? 'Unknown district' }}</p>
                        </div>
                        <span class="status-pill status-{{ $request->status }}">{{ $request->status }}</span>
                    </div>
                    <p>{{ $request->description }}</p>
                    <small>{{ $request->created_at }}</small>
                </div>
            @empty
                <p class="empty-state">No aid requests submitted by you yet.</p>
            @endforelse
        </article>
    </section>
@endsection