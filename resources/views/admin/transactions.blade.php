@extends('admin.layout')

@section('title', 'Transaction Management - DisasterResponseHub')
@section('page-title', 'Transaction Management')
@section('page-subtitle', 'View and manage all donation transactions from ShurjoPay.')

@php
    $activePage = 'transactions';
@endphp

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

    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            align-items: end;
        }

        .form-actions {
            display: flex;
            gap: 8px;
            grid-column: 1 / -1;
        }

        .primary-action,
        .secondary-action {
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
        }

        .primary-action {
            background: #0f766e;
            color: white;
        }

        .primary-action:hover {
            background: #0d5f5d;
        }

        .secondary-action {
            background: #e5e7eb;
            color: #1f2937;
        }

        .secondary-action:hover {
            background: #d1d5db;
        }

        .status-pill {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pill.status-completed {
            background: #d1fae5;
            color: #059669;
        }

        .status-pill.status-pending {
            background: #fef3c7;
            color: #b45309;
        }

        .status-pill.status-failed {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-pill.status-refunded {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .action-link {
            color: #0f766e;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        .table-wrap {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #1f2937;
            font-size: 14px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            color: #374151;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        .pagination-wrap {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 4px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .muted {
            color: #6b7280;
            font-size: 13px;
        }

        code {
            background: #f3f4f6;
            padding: 2px 4px;
            border-radius: 2px;
            font-family: 'Courier New', monospace;
        }
    </style>
@endsection
