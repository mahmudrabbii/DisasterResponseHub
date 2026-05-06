@extends('public.layout')

@section('title', 'Checkout - Disaster Response Hub')

@section('content')
    <div class="payment-container">
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
                        <label for="donor-phone">Phone Number *</label>
                        <input id="donor-phone" type="tel" name="phone" required placeholder="01XXXXXXXXX">
                    </div>

                    <div class="form-group">
                        <label for="donation-amount">Amount (৳) *</label>
                        <input id="donation-amount" type="number" name="amount" required placeholder="500" min="1" step="1">
                    </div>

                    <input type="hidden" name="campaign_id" value="{{ $campaign->id }}">

                    <!-- Submit Button -->
                    <button type="submit" id="submit-btn" class="primary-btn">
                        <span id="button-text">Proceed to Payment</span>
                        <div class="hidden" id="spinner"></div>
                    </button>

                    <!-- Messages -->
                    <div id="payment-message" class="hidden"></div>
                </form>
            </div>

            <p class="payment-note">💡 You'll be redirected to Shurjopay to complete your payment securely.</p>
        </div>
    </div>

    <style>
        .payment-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
        }

        .payment-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #0f766e;
            box-shadow: 0 0 5px rgba(15, 118, 110, 0.3);
        }

        .primary-btn {
            width: 100%;
            padding: 12px;
            background: #0f766e;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .primary-btn:hover {
            background: #0d5f5d;
        }

        .primary-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .hidden { display: none; }

        .payment-note {
            color: #666;
            font-size: 12px;
            margin-top: 20px;
            text-align: center;
        }

        .error-message {
            color: #dc2626;
            padding: 10px;
            background: #fee2e2;
            border-radius: 4px;
            margin-bottom: 15px;
            display: none;
        }

        .success-message {
            color: #059669;
            padding: 10px;
            background: #d1fae5;
            border-radius: 4px;
            margin-bottom: 15px;
            display: none;
        }
    </style>

    <script>
        // Handle form submission
        document.getElementById('payment-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const form = e.target;
            const submitBtn = document.getElementById('submit-btn');
            const amount = parseFloat(document.getElementById('donation-amount').value);
            const email = document.getElementById('donor-email').value;
            const name = document.getElementById('donor-name').value;
            const phone = document.getElementById('donor-phone').value;
            const campaignId = document.querySelector('input[name="campaign_id"]').value;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Processing...</span>';

            try {
                // Create payment session with Shurjopay
                const response = await fetch('{{ route("payment.shurjopay-create") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({
                        amount: amount,
                        donor_email: email,
                        donor_name: name,
                        donor_phone: phone,
                        campaign_id: campaignId,
                    }),
                });

                const data = await response.json();

                if (data.success && data.redirect_url) {
                    // Redirect to Shurjopay checkout
                    window.location.href = data.redirect_url;
                    
                    // After 10 seconds, show a message and redirect to status page
                    // In case ShurjoPay doesn't redirect back
                    setTimeout(() => {
                        // Try to redirect to status page
                        window.location.href = '{{ route("payment.shurjopay-status") }}';
                    }, 10000);
                } else {
                    showError(data.error || 'Failed to create payment session');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span id="button-text">Proceed to Payment</span>';
                }
            } catch (err) {
                showError('An error occurred: ' + err.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span id="button-text">Proceed to Payment</span>';
            }
        });

        function showError(message) {
            const errorDiv = document.getElementById('payment-message');
            errorDiv.textContent = message;
            errorDiv.className = 'error-message';
            errorDiv.style.display = 'block';
        }

        function showSuccess(message) {
            const successDiv = document.getElementById('payment-message');
            successDiv.textContent = message;
            successDiv.className = 'success-message';
            successDiv.style.display = 'block';
        }
    </script>
@endsection
