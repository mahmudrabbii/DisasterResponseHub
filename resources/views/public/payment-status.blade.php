@extends('public.layout')

@section('title', 'Payment Status - Disaster Response Hub')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/payment-status.css') }}">
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
@endsection
