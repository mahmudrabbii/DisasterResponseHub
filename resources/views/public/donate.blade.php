@extends('public.layout')

@section('title', 'Donate - Disaster Response Hub')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/donate.css') }}">
@endpush

@section('content')
    @php
        $activePage = 'donate';
    @endphp

    <div class="donate-container" style="padding: 40px 20px;">
        <h1 style="text-align: center; margin-bottom: 40px;">Active Fundraising Campaigns</h1>

        @if (count($campaigns) > 0)
            <div class="donate-campaigns-grid">
                @foreach ($campaigns as $campaign)
                    <div class="donate-campaign-card">
                        <div class="donate-campaign-header">
                            <h3>{{ $campaign['title'] }}</h3>
                            <p>{{ $campaign['description'] }}</p>
                        </div>

                        <div class="donate-campaign-body">
                            <!-- Progress Bar -->
                            <div class="donate-progress-section">
                                <div class="donate-progress-bar-bg">
                                    @php
                                        $percentage = ($campaign['current_amount'] / $campaign['target_amount']) * 100;
                                        $percentage = min($percentage, 100);
                                    @endphp
                                    <div class="donate-progress-fill" style="width: {{ $percentage }}%;"></div>
                                </div>
                                <div class="donate-progress-stats">
                                    <strong>৳{{ number_format($campaign['current_amount'], 0) }}</strong>
                                    <span>of ৳{{ number_format($campaign['target_amount'], 0) }}</span>
                                </div>
                            </div>

                            <!-- Donors Count -->
                            <div class="donate-donors-count">
                                {{ $campaign['donors_count'] }} supporter(s)
                            </div>

                            <!-- Donate Button -->
                            <a href="{{ route('payment.shurjopay-form', ['campaignId' => $campaign['id']]) }}" class="donate-button">
                                Donate Now
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="donate-empty-state" style="text-align: center; padding: 40px;">
                <p>No active campaigns at the moment. Please check back soon.</p>
            </div>
        @endif
    </div>
@endsection
