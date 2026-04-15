@extends('volunteer ui.layout')

@section('title', 'Aid Request - DisasterResponseHub')
@section('page-title', 'Aid Request')
@section('page-subtitle', 'Create and track aid requests for the people or locations you are supporting.')

@section('content')
    <section class="panel-grid profile-grid">
        <article class="panel-card">
            <h3>Submit a new request</h3>

            <form method="POST" action="{{ route('volunteer.aid-requests.store') }}" class="stack-form">
                @csrf

                <label for="location_id">Location</label>
                <select id="location_id" name="location_id" required>
                    <option value="">Select a location</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->city }}, {{ $location->district }}
                        </option>
                    @endforeach
                </select>

                <label for="aid_type_id">Aid type</label>
                <select id="aid_type_id" name="aid_type_id" required>
                    <option value="">Select an aid type</option>
                    @foreach ($aidTypes as $aidType)
                        <option value="{{ $aidType->id }}" {{ old('aid_type_id') == $aidType->id ? 'selected' : '' }}>
                            {{ $aidType->name }}
                        </option>
                    @endforeach
                </select>

                <label for="description">Description</label>
                <textarea id="description" name="description" rows="6" required>{{ old('description') }}</textarea>

                <button type="submit" class="primary-action">Send request</button>
            </form>
        </article>

        <article class="panel-card">
            <div class="panel-header">
                <h3>My requests</h3>
                <span class="muted">{{ $aidRequests->count() }} total</span>
            </div>

            @forelse ($aidRequests as $request)
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
                <p class="empty-state">No aid requests have been submitted yet.</p>
            @endforelse
        </article>
    </section>
@endsection