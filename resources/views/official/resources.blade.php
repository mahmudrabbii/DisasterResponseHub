@extends('official.layout')

@section('title', 'Resource Handling - DisasterResponseHub')
@section('page-title', 'Resource Handling')
@section('page-subtitle', 'Request resources and record stock usage for each disaster.')

@section('content')
    <section class="panel-grid">
        <article class="panel-card">
            <div class="panel-header">
                <h3>Request resources</h3>
            </div>

            <form method="POST" action="{{ route('official.resource-requests.store') }}" class="stack-form">
                @csrf
                <div class="form-group">
                    <label for="disaster_id">Disaster</label>
                    <select id="disaster_id" name="disaster_id" required>
                        <option value="">Select disaster</option>
                        @foreach ($disasters as $disaster)
                            <option value="{{ $disaster->id }}">{{ $disaster->type }} - {{ $disaster->city ?? 'Unknown city' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="resource_name">Resource name</label>
                    <input id="resource_name" name="resource_name" type="text" required>
                </div>
                <div class="form-group">
                    <label for="quantity_requested">Quantity requested</label>
                    <input id="quantity_requested" name="quantity_requested" type="number" min="1" value="1" required>
                </div>
                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>
                <button type="submit" class="primary-action">Submit request</button>
            </form>
        </article>

        <article class="panel-card">
            <div class="panel-header">
                <h3>Record usage</h3>
            </div>

            <form method="POST" action="{{ route('official.resource-usage.store') }}" class="stack-form">
                @csrf
                <div class="form-group">
                    <label for="usage_disaster_id">Disaster</label>
                    <select id="usage_disaster_id" name="disaster_id" required>
                        <option value="">Select disaster</option>
                        @foreach ($disasters as $disaster)
                            <option value="{{ $disaster->id }}">{{ $disaster->type }} - {{ $disaster->city ?? 'Unknown city' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="resource_id">Stock item</label>
                    <select id="resource_id" name="resource_id">
                        <option value="">Manual entry</option>
                        @foreach ($resources as $resource)
                            <option value="{{ $resource->id }}">{{ $resource->name }} ({{ $resource->quantity }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="usage_resource_name">Resource name</label>
                    <input id="usage_resource_name" name="resource_name" type="text" required>
                </div>
                <div class="form-group">
                    <label for="quantity_used">Quantity used</label>
                    <input id="quantity_used" name="quantity_used" type="number" min="1" value="1" required>
                </div>
                <div class="form-group">
                    <label for="usage_notes">Notes</label>
                    <textarea id="usage_notes" name="notes" rows="3"></textarea>
                </div>
                <button type="submit" class="primary-action">Record usage</button>
            </form>
        </article>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Resource requests</h3>
            <span class="muted">Recent submitted requests</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Disaster</th>
                    <th>Resource</th>
                    <th>Quantity</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($resourceRequests as $request)
                    <tr>
                        <td>{{ $request->disaster_type }}<div class="muted">{{ $request->city ?? 'Unknown city' }}</div></td>
                        <td>{{ $request->resource_name }}</td>
                        <td>{{ $request->quantity_requested }}</td>
                        <td><span class="status-pill status-{{ $request->status }}">{{ $request->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-state">No resource requests recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Disaster</th>
                    <th>Resource</th>
                    <th>Used</th>
                    <th>Recorded by</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($resourceUsage as $usage)
                    <tr>
                        <td>{{ $usage->disaster_type }}<div class="muted">{{ $usage->city ?? 'Unknown city' }}</div></td>
                        <td>{{ $usage->resource_stock_name ?? $usage->resource_name }}</td>
                        <td>{{ $usage->quantity_used }}</td>
                        <td>{{ $usage->recorded_by ?? 'Official' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-state">No usage records available.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection