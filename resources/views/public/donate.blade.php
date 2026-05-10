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
                                <img src="{{ asset('images/disaster.jpg') }}" alt="{{ $campaign['title'] }}" onerror="this.style.display='none'">
                                <svg class="card-image-fallback" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 200" preserveAspectRatio="xMidYMid slice">
                                    <rect fill="#d1fae5" width="400" height="200"/>
                                    <circle cx="80" cy="40" r="25" fill="#0f766e" opacity="0.2"/>
                                    <circle cx="320" cy="160" r="35" fill="#0f766e" opacity="0.15"/>
                                    <path d="M 50 150 Q 100 100 150 120 Q 200 140 250 110 Q 300 80 350 100 L 350 200 L 50 200 Z" fill="#0f766e" opacity="0.1"/>
                                    <text x="200" y="90" font-size="16" fill="#0f766e" text-anchor="middle" font-weight="bold">{{ $campaign['title'] }}</text>
                                    <text x="200" y="115" font-size="12" fill="#0f766e" text-anchor="middle" opacity="0.7">Disaster Relief Campaign</text>
                                </svg>
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
     
@endsection
