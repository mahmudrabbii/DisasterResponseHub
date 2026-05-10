@extends('public.layout')

@section('title', 'Checkout - Disaster Response Hub')

@section('content')    <link rel="stylesheet" href="{{ asset('css/payment.css') }}">    <div class="payment-container">
        <div class="payment-card">
            <h2>Complete Your Donation</h2>
            <p>Campaign: <strong>{{ $campaign->title ?? 'Disaster Relief' }}</strong></p>
            
            <div class="payment-form-wrapper">
                <form id="payment-form">
                    @csrf

                    <!-- Donor Information -->
                    <div class="form-group">
                        <label for="donor-name">Full Name *</label>
                        <input id="donor-name" type="text" name="name" required placeholder="Your name">
                    </div>

                    <div class="form-group">
                        <label for="donor-email">Email *</label>
                        <input id="donor-email" type="email" name="email" required placeholder="your@email.com">
                    </div>

                    <div class="form-group">
                        <label for="donation-amount">Amount (৳) *</label>
                        <input id="donation-amount" type="number" name="amount" required placeholder="500" min="1" step="1">
                    </div>

                    <!-- Stripe Card Element -->
                    <div class="form-group">
                        <label for="card-element">Card Details</label>
                        <div id="card-element"></div>
                        <div id="card-errors" role="alert"></div>
                    </div>

                    <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">

                    <!-- Submit Button -->
                    <button type="submit" id="submit-btn" class="primary-btn">
                        <span id="button-text">Donate</span>
                        <div class="hidden" id="spinner"></div>
                    </button>

                    <!-- Messages -->
                    <div id="payment-message" class="hidden"></div>
                </form>
            </div>

            <p class="payment-note">💡 This is a secure payment. Your card information is encrypted.</p>
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        // Initialize Stripe
        const stripe = Stripe('{{ $stripePublicKey }}');
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        // Handle card errors
        cardElement.on('change', (event) => {
            const errorDiv = document.getElementById('card-errors');
            errorDiv.textContent = event.error ? event.error.message : '';
        });

        // Handle form submission
        document.getElementById('payment-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const form = e.target;
            const submitBtn = document.getElementById('submit-btn');
            const amount = parseFloat(document.getElementById('donation-amount').value);
            const email = document.getElementById('donor-email').value;
            const name = document.getElementById('donor-name').value;
            const campaignId = document.querySelector('input[name="campaign_id"]').value;

            submitBtn.disabled = true;

            // Create payment intent
            try {
                const response = await fetch('{{ route("payment.create-intent") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({
                        amount: amount,
                        email: email,
                        name: name,
                        campaign_id: campaignId,
                    }),
                });

                const data = await response.json();

                if (!data.success) {
                    document.getElementById('card-errors').textContent = data.error;
                    submitBtn.disabled = false;
                    return;
                }

                // Confirm payment
                const { error, paymentIntent } = await stripe.confirmCardPayment(data.clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: {
                            name: name,
                            email: email,
                        },
                    },
                });

                if (error) {
                    document.getElementById('card-errors').textContent = error.message;
                    submitBtn.disabled = false;
                } else if (paymentIntent.status === 'succeeded') {
                    // Redirect to success
                    window.location.href = '{{ route("payment.success") }}?intent_id=' + paymentIntent.id + '&amount=' + amount + '&email=' + email + '&name=' + name + '&campaign_id=' + campaignId;
                }
            } catch (err) {
                document.getElementById('card-errors').textContent = 'An error occurred: ' + err.message;
                submitBtn.disabled = false;
            }
        });
    </script>
@endsection
