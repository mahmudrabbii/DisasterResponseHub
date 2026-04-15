@extends('admin.layout')

@section('title', 'Manage Users - DisasterResponseHub')
@section('page-title', 'Manage Users')
@section('page-subtitle', 'Add, update, view, and delete official and volunteer accounts.')

@section('content')
    @php
        $editingUser = null;
        if (request()->filled('edit')) {
            $editingUser = $users->firstWhere('user_id', (int) request('edit'));
        }
    @endphp

    <section class="panel-card">
        <div class="panel-header">
            <h3>{{ $editingUser ? 'Edit user' : 'Add user' }}</h3>
            @if ($editingUser)
                <a href="{{ route('admin.users') }}">Cancel edit</a>
            @endif
        </div>

        <form method="POST" action="{{ $editingUser ? route('admin.users.update', $editingUser->user_id) : route('admin.users.store') }}" class="form-grid">
            @csrf
            @if ($editingUser)
                @method('PATCH')
            @endif

            <div class="form-group">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $editingUser->name ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $editingUser->email ?? '') }}" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $editingUser->phone ?? '') }}" placeholder="Optional">
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    @php($roleValue = old('role', $editingUser->role ?? 'official'))
                    <option value="admin" {{ $roleValue === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="official" {{ $roleValue === 'official' ? 'selected' : '' }}>NGO Official</option>
                    <option value="volunteer" {{ $roleValue === 'volunteer' ? 'selected' : '' }}>Volunteer</option>
                </select>
            </div>

            <div class="form-group form-wide">
                <label for="password">Password {{ $editingUser ? '(leave blank to keep current)' : '' }}</label>
                <input id="password" name="password" type="password" {{ $editingUser ? '' : 'required' }}>
            </div>

            <div class="form-actions form-wide">
                <button type="submit" class="primary-action">{{ $editingUser ? 'Update user' : 'Create user' }}</button>
            </div>
        </form>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>All users</h3>
            <span class="muted">{{ $users->count() }} records</span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? 'N/A' }}</td>
                        <td><span class="status-pill status-{{ $user->role }}">{{ $user->role }}</span></td>
                        <td>{{ $user->created_at }}</td>
                        <td class="actions-cell">
                            <a class="action-link" href="{{ route('admin.users', ['edit' => $user->user_id]) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user->user_id) }}" class="inline-form" onsubmit="return confirm('Delete this user and linked records?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="danger-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No users found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection