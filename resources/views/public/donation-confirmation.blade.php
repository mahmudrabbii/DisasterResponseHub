@extends('public.layout')

@section('title', 'Donation Confirmed - Disaster Response Hub')

@section('content')    <link rel="stylesheet" href="{{ asset('css/donation-confirmation.css') }}">    <div class="confirmation-container">
        <div class="confirmation-card">
            <!-- Success Icon -->
            <div class="confirmation-icon success">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                </svg>
            </div>

            <!-- Title -->
            <h1>Donation Confirmed!</h1>
            <p class="subtitle">Thank you for your generous support. Your donation has been successfully processed.</p>

            <!-- Transaction Details -->
            <section class="details-section">
                <h2>Donation Details</h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="label">Amount Donated</span>
                        <span class="value amount">৳{{ number_format($transaction->amount, 2) }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Campaign</span>
                        <span class="value">{{ $campaign->title ?? 'Disaster Relief' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Payment Method</span>
                        <span class="value">{{ ucfirst($transaction->payment_method) }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Transaction ID</span>
                        <span class="value transaction-id">{{ $transaction->order_id }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Donor Name</span>
                        <span class="value">{{ $transaction->donor_name }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Donor Email</span>
                        <span class="value">{{ $transaction->donor_email }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Date & Time</span>
                        <span class="value">{{ \Carbon\Carbon::parse($transaction->created_at)->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Status</span>
                        <span class="value status-badge status-{{ $transaction->status }}">{{ ucfirst($transaction->status) }}</span>
                    </div>
                </div>
            </section>

            <!-- Impact Section -->
            <section class="impact-section">
                <h2>Your Impact</h2>
                <p class="impact-text">Your donation of <strong>৳{{ number_format($transaction->amount, 0) }}</strong> will directly support the affected communities. Every contribution helps us:</p>
                <ul class="impact-list">
                    <li>Provide emergency relief supplies</li>
                    <li>Support displaced families</li>
                    <li>Fund rescue and recovery operations</li>
                    <li>Support long-term rehabilitation efforts</li>
                </ul>
            </section>

            <!-- Receipt Information -->
            <section class="receipt-section">
                <h2>Receipt & Confirmation</h2>
                <!--
                <p>A detailed receipt has been sent to <strong>{{ $transaction->donor_email }}</strong></p>
                -->
                <p>A detailed receipt can be download from below</strong></p>
                <!--
                <p class="receipt-note">Please check your inbox for the official receipt. If you don't receive it within a few minutes, please check your spam folder.</p>
                -->
                <button class="print-btn" onclick="window.print()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Print Receipt
                </button>
            </section>

            <!-- Call to Action -->
            <section class="cta-section">
                <p class="cta-text">Help us reach more people in need</p>
                <div class="cta-buttons">
                    <a href="{{ route('public.donate') }}" class="btn btn-primary">Make Another Donation</a>
                    <a href="{{ route('public.home') }}" class="btn btn-secondary">Return to Home</a>
                </div>
            </section>

            <!-- Gratitude Message -->
            <div class="gratitude">
                <p>🙏 Thank you for being a the hope for disaster affected communities!</p>
            </div>
        </div>
    </div>

@endsection
