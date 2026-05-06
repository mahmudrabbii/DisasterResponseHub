@extends('public.layout')

@section('title', 'Payment Status - Disaster Response Hub')

@section('content')
    <div class="payment-status-container">
        <div class="payment-status-card">
            @if (isset($error))
                <!-- Error State -->
                <div class="status-icon error">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                </div>
                <h2>Payment Error</h2>
                <p class="error-message">{{ $error }}</p>
                <a href="{{ route('public.donate') }}" class="primary-btn">Return to Donations</a>
            @elseif ($transaction->status === 'completed')
                <!-- Success State - Auto Redirect -->
                <div class="status-icon success">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                    </svg>
                </div>
                <h2>Payment Confirmed!</h2>
                <p class="success-message">Your payment has been confirmed. Redirecting...</p>
                <div class="spinner"></div>
                <script>
                    setTimeout(() => {
                        window.location.href = "{{ route('payment.confirmation', ['orderId' => $order_id]) }}";
                    }, 2000);
                </script>
            @elseif ($transaction->status === 'failed')
                <!-- Failed State -->
                <div class="status-icon error">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>
                    </svg>
                </div>
                <h2>Payment Failed</h2>
                <p class="error-message">Your payment could not be processed. Please try again or contact support.</p>
                <a href="{{ route('public.donate') }}" class="primary-btn">Retry Payment</a>
            @else
                <!-- Pending State - Check Status -->
                <div class="status-icon pending">
                    <div class="spinner-circle"></div>
                </div>
                <h2>Verifying Payment</h2>
                <p class="status-message">{{ $message ?? 'Please wait while we verify your payment...' }}</p>
                <div class="status-details">
                    <div class="detail-row">
                        <span class="label">Order ID:</span>
                        <span class="value">{{ $order_id }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Amount:</span>
                        <span class="value">৳{{ number_format($transaction->amount, 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Status:</span>
                        <span class="value status-badge status-{{ $transaction->status }}">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button onclick="location.reload()" class="secondary-btn">Check Again</button>
                    <a href="{{ route('public.donate') }}" class="primary-btn">Return to Donations</a>
                </div>

                <!-- Auto-refresh every 3 seconds -->
                <script>
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                </script>
            @endif
        </div>
    </div>

    <style>
        .payment-status-container {
            max-width: 500px;
            margin: 60px auto;
            padding: 20px;
        }

        .payment-status-card {
            background: white;
            border-radius: 8px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .status-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 40px;
        }

        .status-icon.success {
            background: #d1fae5;
            color: #059669;
        }

        .status-icon.error {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-icon.pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-icon svg {
            width: 100%;
            height: 100%;
        }

        .spinner-circle {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(217, 119, 6, 0.1);
            border-top-color: #d97706;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .payment-status-card h2 {
            font-size: 24px;
            margin: 20px 0 10px;
            color: #333;
        }

        .payment-status-card p {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .error-message {
            color: #dc2626;
            background: #fee2e2;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .success-message {
            color: #059669;
            background: #d1fae5;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .status-message {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .status-details {
            background: #f9fafb;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row .label {
            font-weight: 600;
            color: #666;
        }

        .detail-row .value {
            color: #333;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-badge.status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-badge.status-failed {
            background: #fee2e2;
            color: #7f1d1d;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .primary-btn,
        .secondary-btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
        }

        .primary-btn {
            background: #0f766e;
            color: white;
        }

        .primary-btn:hover {
            background: #0d5f5d;
        }

        .secondary-btn {
            background: #e5e7eb;
            color: #333;
        }

        .secondary-btn:hover {
            background: #d1d5db;
        }

        .spinner {
            width: 30px;
            height: 30px;
            border: 3px solid #0f766e;
            border-top: 3px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
    </style>
@endsection
