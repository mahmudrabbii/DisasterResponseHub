@extends('admin.layout')

@section('title', 'Transaction Management - DisasterResponseHub')
@section('page-title', 'Transaction Management')
@section('page-subtitle', 'View and manage all donation transactions from ShurjoPay.')

@php
    $activePage = 'transactions';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-transactions.css') }}">
@endpush

@section('content')
    <section class="panel-card">
        <div class="panel-header">
            <h3>Filter Transactions</h3>
            <span class="muted">Search and filter transaction records</span>
        </div>

        <form method="GET" action="{{ route('admin.transactions') }}" class="form-grid">
            <div class="form-group">
                <label for="search">Search by name or email</label>
                <input id="search" name="search" type="text" placeholder="Donor name or email" value="{{ request('search') }}">
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All statuses</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                    <option value="refunded" @selected(request('status') === 'refunded')>Refunded</option>
                </select>
            </div>

            <div class="form-group">
                <label for="method">Payment Method</label>
                <select id="method" name="method">
                    <option value="">All methods</option>
                    <option value="shurjopay" @selected(request('method') === 'shurjopay')>ShurjoPay</option>
                    <option value="stripe" @selected(request('method') === 'stripe')>Stripe</option>
                    <option value="paypal" @selected(request('method') === 'paypal')>PayPal</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="primary-action">Filter Transactions</button>
                <a href="{{ route('admin.transactions') }}" class="secondary-action">Clear filters</a>
            </div>
        </form>
    </section>

    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>All Transactions</h3>
            <span class="muted">{{ $transactions->total() }} total transaction(s)</span>
        </div>

        @if ($transactions->count() > 0)
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Donor</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Campaign</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Order ID</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y H:i') }}</td>
                            <td>
                                <strong>{{ $transaction->donor_name }}</strong>
                            </td>
                            <td>{{ $transaction->donor_email }}</td>
                            <td>{{ $transaction->donor_phone ?? 'N/A' }}</td>
                            <td>
                                <strong>৳{{ number_format($transaction->amount, 2) }}</strong>
                            </td>
                            <td>
                                @if ($transaction->campaign_title)
                                    {{ $transaction->campaign_title }}
                                @else
                                    <span class="muted">N/A</span>
                                @endif
                            </td>
                            <td>{{ ucfirst($transaction->payment_method) }}</td>
                            <td>
                                <span class="status-pill status-{{ $transaction->status }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </td>
                            <td>
                                <code style="font-size: 11px; word-break: break-all;">{{ $transaction->order_id }}</code>
                            </td>
                            <td>
                                <a href="#" class="action-link" title="View details">View</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrap">
                {{ $transactions->links() }}
            </div>
        @else
            <p class="empty-state">No transactions found matching your filters.</p>
        @endif
    </section>
@endsection
