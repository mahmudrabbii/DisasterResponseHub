@extends('admin.layout')

@section('title', 'Resource Inventory - DisasterResponseHub')
@section('page-title', 'Resource Inventory')
@section('page-subtitle', 'Add, update, view, and delete relief stock records.')

@section('content')
    @php
        $editingResource = null;
        if (request()->filled('edit')) {
            $editingResource = $resources->firstWhere('id', (int) request('edit'));
        }
    @endphp

    <section class="panel-card">
        <div class="panel-header">
            <h3>{{ $editingResource ? 'Edit stock item' : 'Add stock item' }}</h3>
            @if ($editingResource)
                <a href="{{ route('admin.resources') }}">Cancel edit</a>
            @endif
        </div>

        <form method="POST" action="{{ $editingResource ? route('admin.resources.update', $editingResource->id) : route('admin.resources.store') }}" class="form-grid">
            @csrf
            @if ($editingResource)
                @method('PATCH')
            @endif

            <div class="form-group">
                <label for="name">Item name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $editingResource->name ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <input id="category" name="category" type="text" value="{{ old('category', $editingResource->category ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input id="quantity" name="quantity" type="number" min="0" value="{{ old('quantity', $editingResource->quantity ?? 0) }}" required>
            </div>

            <div class="form-group">
                <label for="expiry_date">Expiry date</label>
                <input id="expiry_date" name="expiry_date" type="date" value="{{ old('expiry_date', $editingResource->expiry_date ?? '') }}">
            </div>

            <div class="form-actions form-wide">
                <button type="submit" class="primary-action">{{ $editingResource ? 'Update item' : 'Create item' }}</button>
            </div>
        </form>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Stock list</h3>
            <span class="muted">{{ $resources->count() }} records</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Expiry</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($resources as $resource)
                    <tr>
                        <td>{{ $resource->name }}</td>
                        <td>{{ $resource->category }}</td>
                        <td>{{ $resource->quantity }}</td>
                        <td>{{ $resource->expiry_date ?? 'N/A' }}</td>
                        <td>{{ $resource->created_at }}</td>
                        <td class="actions-cell">
                            <a class="action-link" href="{{ route('admin.resources', ['edit' => $resource->id]) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.resources.destroy', $resource->id) }}" class="inline-form" onsubmit="return confirm('Delete this stock item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="danger-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No stock items found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection