@extends('public.layout')

@section('title', 'Donation Confirmed - Disaster Response Hub')

@section('content')
    <div class="confirmation-container">
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
                <p>🙏 Thank you for being a the hope for disaster-affected communities!</p>
            </div>
        </div>
    </div>

    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #0d5f5d;
            --success: #059669;
            --text: #1f2937;
            --text-light: #6b7280;
            --border: #e5e7eb;
            --bg-light: #f9fafb;
        }

        .confirmation-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
        }

        .confirmation-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .confirmation-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .confirmation-icon.success {
            background: #d1fae5;
            color: var(--success);
        }

        .confirmation-card h1 {
            font-size: 32px;
            color: var(--text);
            margin: 0 0 8px;
            font-weight: 700;
        }

        .subtitle {
            color: var(--text-light);
            font-size: 16px;
            margin: 0 0 32px;
        }

        .details-section,
        .impact-section,
        .receipt-section,
        .cta-section {
            text-align: left;
            margin: 32px 0;
            padding: 24px;
            background: var(--bg-light);
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .details-section h2,
        .impact-section h2,
        .receipt-section h2,
        .cta-section h2 {
            font-size: 18px;
            color: var(--text);
            margin: 0 0 16px;
            font-weight: 600;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .detail-item {
            padding: 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid var(--border);
        }

        .detail-item .label {
            display: block;
            font-size: 12px;
            color: var(--text-light);
            text-transform: uppercase;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .detail-item .value {
            display: block;
            font-size: 16px;
            color: var(--text);
            font-weight: 600;
            word-break: break-all;
        }

        .detail-item .value.amount {
            font-size: 20px;
            color: var(--success);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.status-completed {
            background: #d1fae5;
            color: var(--success);
        }

        .status-badge.status-pending {
            background: #fef3c7;
            color: #b45309;
        }

        .transaction-id {
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }

        .impact-section p {
            color: var(--text-light);
            margin: 0 0 12px;
            line-height: 1.6;
        }

        .impact-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .impact-list li {
            padding: 8px 0;
            padding-left: 24px;
            position: relative;
            color: var(--text);
        }

        .impact-list li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--success);
            font-weight: bold;
        }

        .receipt-section p {
            margin: 0 0 8px;
            color: var(--text-light);
            font-size: 14px;
        }

        .receipt-note {
            color: #ea580c;
            font-size: 13px;
            margin-top: 12px;
            padding: 8px;
            background: #fef3c7;
            border-radius: 4px;
        }

        .print-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            transition: background 0.2s;
        }

        .print-btn:hover {
            background: var(--primary-dark);
        }

        .print-btn svg {
            width: 16px;
            height: 16px;
        }

        .cta-section {
            text-align: center;
        }

        .cta-text {
            color: var(--text);
            font-weight: 600;
            margin-bottom: 16px;
        }

        .cta-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: var(--border);
            color: var(--text);
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .gratitude {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            font-size: 18px;
            color: var(--primary);
            font-weight: 600;
        }

        @media (max-width: 640px) {
            .confirmation-card {
                padding: 24px;
            }

            .confirmation-card h1 {
                font-size: 24px;
            }

            .details-grid {
                grid-template-columns: 1fr;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }

        @media print {
            .print-btn,
            .cta-buttons {
                display: none;
            }

            .confirmation-card {
                box-shadow: none;
                border: 1px solid var(--border);
            }
        }
    </style>
@endsection
