@extends('admin.layout')

@section('title', 'Review Help Request - DisasterResponseHub')
@section('page-title', 'Help Request Detail')

@section('content')
    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>{{ $request_data->name }} - Help Request</h3>
            <a href="{{ route('admin.public-help-requests') }}">Back to requests</a>
        </div>

        <div class="panel-grid">
            <div class="list-row">
                <strong>Requester Name</strong>
                <p>{{ $request_data->name }}</p>
            </div>

            <div class="list-row">
                <strong>Email Address</strong>
                <p>{{ $request_data->email }}</p>
            </div>

            <div class="list-row">
                <strong>Phone Number</strong>
                <p>{{ $request_data->phone ?? 'Not provided' }}</p>
            </div>

            <div class="list-row">
                <strong>Location</strong>
                <p>{{ $request_data->city ?? 'Unknown' }}, {{ $request_data->district ?? 'Unknown' }}</p>
            </div>

            <div class="list-row">
                <strong>Aid Types Needed</strong>
                <p>
                    @foreach ($aidTypes as $aidType)
                        <span class="status-pill aid-type-tag">{{ $aidType }}</span>
                    @endforeach
                </p>
            </div>

            <div class="list-row">
                <strong>Current Status</strong>
                <p><span class="status-pill status-{{ $request_data->status }}">{{ ucfirst($request_data->status) }}</span></p>
            </div>

            <div class="list-row">
                <strong>Request Date</strong>
                <p>{{ \Carbon\Carbon::parse($request_data->created_at)->format('M d, Y \a\t H:i') }}</p>
            </div>
        </div>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Request Description</h3>
        </div>

        <div class="section-description">
            <p class="description-text">{{ $request_data->description }}</p>
        </div>
    </section>

    <section class="panel-card">
        <div class="panel-header">
            <h3>Update Status</h3>
        </div>

        <form method="POST" action="{{ route('admin.public-help-requests.update', $request_data->id) }}" class="form-grid">
            @csrf
            @method('PATCH')

            <div class="form-group form-wide">
                <label for="status">Request Status</label>
                <select id="status" name="status" required>
                    <option value="pending" @selected('pending')>Pending</option>
                    <option value="approved" @selected('approved')>Approved</option>
                    <option value="rejected" @selected('rejected')>Rejected</option>
                    <option value="completed" @selected('completed')>Completed</option>
                </select>
            </div>

            <div class="form-actions form-wide">
                <button type="submit" class="primary-action">Update Status</button>
                <a href="{{ route('admin.public-help-requests') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
