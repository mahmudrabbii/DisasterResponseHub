@extends('public.layout')

@section('title', 'Donate - Disaster Response Hub')

@section('content')
    @php
        $activePage = 'donate';
    @endphp

    <div class="page-title-section">
        <h1>Support Our Mission</h1>
        <p>Help us provide relief and support to communities affected by disasters</p>
    </div>

    <!-- Donation Statistics -->
    <div class="donation-stats">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($totalRaised ?? 0, 0) }}</div>
            <div class="stat-label">Amount Raised (৳)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($totalDonors ?? 0) }}</div>
            <div class="stat-label">Active Donors</div>
        </div>
    </div>

    <!-- Donation Campaigns -->
    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Active Campaigns</h3>
        </div>

        @if (count($campaigns) > 0)
            <div class="campaigns-grid">
                @foreach ($campaigns as $campaign)
                    <div class="campaign-card">
                        <h4>{{ $campaign['title'] }}</h4>
                        <p class="campaign-description">{{ $campaign['description'] }}</p>

                        <div class="progress-section">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: {{ ($campaign['current_amount'] / $campaign['target_amount']) * 100 }}%"></div>
                            </div>
                            <div class="progress-stats">
                                <span class="current-amount">{{ number_format($campaign['current_amount'], 0) }} ৳</span>
                                <span class="target-amount">of {{ number_format($campaign['target_amount'], 0) }} ৳</span>
                            </div>
                        </div>

                        <div class="campaign-footer">
                            <small class="donors-count">{{ number_format($campaign['donors_count']) }} donors</small>
                            <button class="donate-btn">Donate Now</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state-container">
                <p class="empty-state">No active campaigns at the moment. Check back soon!</p>
            </div>
        @endif
    </section>

    <!-- Donation Call to Action -->
    <section class="panel-card full-width donation-cta">
        <div class="cta-content">
            <h3>Make a Difference Today</h3>
            <p>Your donation directly helps us provide emergency relief, medical support, and essential supplies to families affected by disasters.</p>
            <button class="primary-action large-btn">Donate via bKash / Card</button>
        </div>
    </section>

    <!-- Recent Donations 
    <section class="panel-card full-width">
        <div class="panel-header">
            <h3>Recent Donors</h3>
        </div>

        @if ($recentDonations->count() > 0)
            <div class="donations-timeline">
                @foreach ($recentDonations as $donation)
                    <div class="donation-item">
                        <div class="donation-avatar">
                            <div class="avatar-circle">{{ strtoupper(substr($donation->donor ?? 'A', 0, 1)) }}</div>
                        </div>
                        <div class="donation-details">
                            <div class="donation-donor">
                                <strong>{{ $donation->donor ?? 'Anonymous' }}</strong>
                                <span class="donation-time">{{ \Carbon\Carbon::parse($donation->created_at)->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="donation-amount">
                            <strong>{{ number_format($donation->amount, 0) }} ৳</strong>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state-container">
                <p class="empty-state">No donations yet. Be the first to donate!</p>
            </div>
        @endif
    </section>
    -->

@endsection
