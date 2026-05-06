@extends('public.layout')

@section('title', 'Donate - Disaster Response Hub')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/donate.css') }}">
@endpush

@section('content')
    @php
        $activePage = 'donate';
    @endphp

    <!-- Hero Section -->
    <section class="donation-hero">
        <div class="hero-content">
            <h1>Make a Difference Today</h1>
            <p>Your generous donation directly supports disaster relief efforts and helps communities in crisis recover and rebuild.</p>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ count($campaigns) }}</span>
                    <span class="stat-label">Active Campaigns</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">৳{{ number_format(collect($campaigns)->sum('current_amount'), 0) }}</span>
                    <span class="stat-label">Funds Raised</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ collect($campaigns)->sum('donors_count') }}</span>
                    <span class="stat-label">Supporters</span>
                </div>
            </div>
        </div>
        <div class="hero-decoration"></div>
    </section>

    <!-- Main Donation Section -->
    <section class="donation-section">
        <div class="donation-container">
            @if (count($campaigns) > 0)
                <!-- Section Header -->
                <div class="section-header">
                    <h2>Active Fundraising Campaigns</h2>
                    <p>Select a campaign and contribute to help those in need</p>
                </div>

                <!-- Campaigns Grid -->
                <div class="campaigns-grid">
                    @foreach ($campaigns as $campaign)
                        <div class="campaign-card">
                            <div class="card-image">
                                <img src="{{ asset('images/background.jpg') }}" alt="{{ $campaign['title'] }}" onerror="this.style.display='none'">
                                <svg class="card-image-fallback" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 200" preserveAspectRatio="xMidYMid slice">
                                    <rect fill="#d1fae5" width="400" height="200"/>
                                    <circle cx="80" cy="40" r="25" fill="#0f766e" opacity="0.2"/>
                                    <circle cx="320" cy="160" r="35" fill="#0f766e" opacity="0.15"/>
                                    <path d="M 50 150 Q 100 100 150 120 Q 200 140 250 110 Q 300 80 350 100 L 350 200 L 50 200 Z" fill="#0f766e" opacity="0.1"/>
                                    <text x="200" y="90" font-size="16" fill="#0f766e" text-anchor="middle" font-weight="bold">{{ $campaign['title'] }}</text>
                                    <text x="200" y="115" font-size="12" fill="#0f766e" text-anchor="middle" opacity="0.7">Disaster Relief Campaign</text>
                                </svg>
                                <div class="progress-badge">
                                    @php
                                        $percentage = ($campaign['current_amount'] / $campaign['target_amount']) * 100;
                                        $percentage = min($percentage, 100);
                                    @endphp
                                    {{ number_format($percentage, 0) }}%
                                </div>
                            </div>

                            <div class="card-top">
                                <h3 class="campaign-title">{{ $campaign['title'] }}</h3>
                            </div>

                            <div class="card-stats">
                                <div class="stat">
                                    <small>Raised</small>
                                    <strong>৳{{ number_format($campaign['current_amount'], 0) }}</strong>
                                </div>
                                <div class="stat">
                                    <small>Goal</small>
                                    <strong>৳{{ number_format($campaign['target_amount'], 0) }}</strong>
                                </div>
                                <div class="stat">
                                    <small>Supporters</small>
                                    <strong>{{ $campaign['donors_count'] }}</strong>
                                </div>
                            </div>

                            <a href="{{ route('payment.shurjopay-form', ['campaignId' => $campaign['id']]) }}" class="donate-btn-compact">
                                Donate Now
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                        </svg>
                    </div>
                    <h3>No Active Campaigns</h3>
                    <p>There are currently no active fundraising campaigns. Please check back soon to support disaster relief efforts.</p>
                    <a href="{{ route('public.home') }}" class="empty-state-btn">Back to Home</a>
                </div>
            @endif
        </div>
    </section>

    <!-- Impact Section
    <section class="impact-section">
        <div class="donation-container">
            <h2>Your Impact Matters</h2>
            <div class="impact-grid">
                <div class="impact-card">
                    <div class="impact-number">50</div>
                    <div class="impact-description">Meals provided to affected families</div>
                </div>
                <div class="impact-card">
                    <div class="impact-number">100</div>
                    <div class="impact-description">Emergency supplies distributed</div>
                </div>
                <div class="impact-card">
                    <div class="impact-number">200</div>
                    <div class="impact-description">People supported in relief efforts</div>
                </div>
                <div class="impact-card">
                    <div class="impact-number">1000</div>
                    <div class="impact-description">Crisis counseling hours provided</div>
                </div>
            </div>
        </div>
    </section>
-->
    <!-- CTA Section
    <section class="donation-cta-section">
        <div class="donation-container">
            <div class="cta-content">
                <h2>Every Contribution Counts</h2>
                <p>Whether you donate ৳100 or ৳10,000, your support makes a real difference in the lives of people affected by disasters. Together, we can help communities recover and rebuild.</p>
                <p class="cta-security">🔒 All donations are secure and processed through Shurjopay with bank-level encryption</p>
            </div>
        </div>
    </section>
 -->
     

    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #0d5f5d;
            --primary-light: #f0fdf4;
            --secondary: #ea580c;
            --success: #059669;
            --text: #1f2937;
            --text-light: #6b7280;
            --border: #e5e7eb;
            --bg-light: #f9fafb;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        /* Hero Section */
        .donation-hero {
            background: linear-gradient(135deg, var(--primary) 0%, #0a4f4a 100%);
            color: white;
            padding: 80px 20px;
            position: relative;
            overflow: hidden;
        }

        .donation-hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(100px, -100px);
        }

        .hero-content {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .donation-hero h1 {
            font-size: 48px;
            margin: 0 0 20px 0;
            font-weight: 700;
            line-height: 1.2;
        }

        .donation-hero > p {
            font-size: 18px;
            margin: 0 0 50px 0;
            opacity: 0.95;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.15);
            padding: 25px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-number {
            display: block;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-label {
            display: block;
            font-size: 14px;
            opacity: 0.9;
        }

        /* Main Section */
        .donation-section {
            padding: 60px 20px;
            background: var(--bg-light);
        }

        .donation-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: 36px;
            color: var(--text);
            margin: 0 0 12px 0;
            font-weight: 700;
        }

        .section-header p {
            font-size: 18px;
            color: var(--text-light);
            margin: 0;
        }

        /* Campaigns Grid */
        .campaigns-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        /* Campaign Card */
        .campaign-card {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .campaign-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .card-image {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
            background: linear-gradient(135deg, #f0fdf4, #d1fae5);
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .card-image-fallback {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .campaign-card:hover .card-image img {
            transform: scale(1.05);
        }

        .progress-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: var(--primary);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: var(--shadow-md);
        }

        .card-top {
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
        }

        .campaign-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin: 0;
            line-height: 1.3;
            flex: 1;
        }

        /* Progress Bar */
        .progress-bar-wrapper {
            display: none;
        }

        .progress-bar-bg {
            display: none;
        }

        .progress-bar-fill {
            display: none;
        }

        /* Card Stats */
        .card-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin: 0;
            padding: 0 16px 12px;
            border-bottom: 1px solid var(--border);
        }

        .stat {
            display: flex;
            flex-direction: column;
            gap: 2px;
            text-align: center;
        }

        .stat small {
            font-size: 11px;
            color: var(--text-light);
            font-weight: 500;
        }

        .stat strong {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }

        /* Donate Button */
        .donate-btn-compact {
            display: block;
            width: calc(100% - 32px);
            margin: 0 16px 16px;
            padding: 12px 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s ease;
        }

        .donate-btn-compact:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 118, 110, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: var(--primary-light);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
        }

        .empty-state-icon svg {
            width: 40px;
            height: 40px;
        }

        .empty-state h3 {
            font-size: 24px;
            color: var(--text);
            margin: 0 0 12px 0;
        }

        .empty-state p {
            font-size: 16px;
            color: var(--text-light);
            margin: 0 0 28px 0;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .empty-state-btn {
            display: inline-block;
            padding: 12px 28px;
            background: var(--primary);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .empty-state-btn:hover {
            background: var(--primary-dark);
        }

        /* Impact Section */
        .impact-section {
            padding: 60px 20px;
            background: white;
        }

        .impact-section h2 {
            text-align: center;
            font-size: 36px;
            color: var(--text);
            margin: 0 0 50px 0;
            font-weight: 700;
        }

        .impact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }

        .impact-card {
            background: var(--primary-light);
            padding: 32px 24px;
            border-radius: 12px;
            text-align: center;
            border-left: 4px solid var(--primary);
        }

        .impact-number {
            font-size: 44px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .impact-description {
            font-size: 15px;
            color: var(--text);
            font-weight: 500;
        }

        /* CTA Section */
        .donation-cta-section {
            padding: 60px 20px;
            background: linear-gradient(135deg, var(--primary) 0%, #0a4f4a 100%);
            color: white;
        }

        .cta-content {
            text-align: center;
            max-width: 700px;
            margin: 0 auto;
        }

        .cta-content h2 {
            font-size: 32px;
            margin: 0 0 16px 0;
            font-weight: 700;
        }

        .cta-content p {
            font-size: 16px;
            margin: 0 0 16px 0;
            opacity: 0.95;
            line-height: 1.6;
        }

        .cta-security {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 24px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .donation-hero {
                padding: 50px 20px;
            }

            .donation-hero h1 {
                font-size: 32px;
            }

            .donation-hero > p {
                font-size: 16px;
            }

            .hero-stats {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .campaigns-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .section-header h2 {
                font-size: 28px;
            }

            .impact-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .cta-content h2 {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .donation-hero {
                padding: 40px 15px;
            }

            .donation-hero h1 {
                font-size: 24px;
            }

            .donation-hero > p {
                font-size: 14px;
            }

            .stat-item {
                padding: 15px;
            }

            .stat-number {
                font-size: 28px;
            }

            .campaigns-grid {
                grid-template-columns: 1fr;
            }

            .impact-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                margin-bottom: 30px;
            }

            .campaign-card {
                border-radius: 8px;
            }
        }
    </style>
@endsection
