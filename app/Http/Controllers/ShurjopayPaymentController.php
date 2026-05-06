<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ShurjoPayService;

class ShurjopayPaymentController extends Controller
{
    protected $shurjoPay;

    public function __construct(ShurjoPayService $shurjoPay)
    {
        $this->shurjoPay = $shurjoPay;
    }

    /**
     * Show payment form for a specific campaign
     */
    public function showPaymentForm($campaignId)
    {
        $campaign = DB::table('fundraising as f')
            ->leftJoin('disasters as d', 'f.disaster_id', '=', 'd.id')
            ->leftJoin('locations as l', 'd.location_id', '=', 'l.id')
            ->where('f.id', $campaignId)
            ->where('f.status', 'active')
            ->select(
                'f.id',
                'f.title',
                'f.disaster_id',
                'd.type as disaster_type',
                'l.city',
                'l.district'
            )
            ->first();

        if (!$campaign) {
            return redirect()->route('public.donate')->with('error', 'Campaign not found');
        }

        return view('public.payment-shurjopay', ['campaign' => $campaign]);
    }

    /**
     * Create payment session
     */
    public function createPaymentSession(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => ['required', 'integer', 'exists:fundraising,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'donor_name' => ['required', 'string', 'max:100'],
            'donor_email' => ['required', 'email', 'max:100'],
            'donor_phone' => ['required', 'string', 'max:20'],
        ]);

        try {
            // Generate a local order ID for internal tracking
            $localOrderId = config('services.shurjopay.prefix') . '_' . uniqid();

            // Prepare payment data using official plugin structure
            $paymentData = [
                'amount' => $validated['amount'],
                'currency' => 'BDT',
                'customer_name' => $validated['donor_name'],
                'customer_email' => $validated['donor_email'],
                'customer_phone' => $validated['donor_phone'],
                'customer_address' => $validated['donor_phone'],
                'customer_city' => 'Dhaka',
                'customer_country' => 'Bangladesh',
                'discount_amount' => 0,
                'disc_percent' => 0,
                // Custom fields for storing additional data
                'value1' => json_encode([
                    'campaign_id' => $validated['campaign_id'],
                    'local_order_id' => $localOrderId,
                ]),
            ];

            \Log::info('Creating payment with data:', $paymentData);

            // Create payment session with Shurjopay using official plugin
            try {
                $response = $this->shurjoPay->createPayment($paymentData);
            } catch (\Exception $e) {
                \Log::error('ShurjoPay createPayment exception:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return response()->json(['error' => 'Payment service error: ' . $e->getMessage()], 500);
            }

            \Log::info('Shurjopay Payment Response:', is_array($response) ? $response : (array) $response);

            // Check if response is valid
            if (empty($response)) {
                return response()->json(['error' => 'No response from payment gateway'], 500);
            }

            // Convert stdObject to array if needed
            $response = is_object($response) ? (array) $response : $response;

            if (isset($response['error'])) {
                return response()->json(['error' => 'Failed to create payment session: ' . $response['error']], 500);
            }

            // Use ShurjoPay's sp_order_id as the order_id
            // This is important for callback verification
            $orderId = $response['sp_order_id'] ?? $response['order_id'] ?? $localOrderId;

            \Log::info('Order ID Assignment', [
                'local_order_id' => $localOrderId,
                'shurjopay_order_id' => $orderId,
                'using' => $orderId === $localOrderId ? 'local' : 'shurjopay',
            ]);

            // Store transaction record with ShurjoPay's order ID
            DB::table('transactions')->insert([
                'campaign_id' => $validated['campaign_id'],
                'order_id' => $orderId,
                'amount' => $validated['amount'],
                'donor_name' => $validated['donor_name'],
                'donor_email' => $validated['donor_email'],
                'donor_phone' => $validated['donor_phone'],
                'status' => 'pending',
                'payment_method' => 'shurjopay',
                'payment_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Log::info('Payment Session - Transaction Created', [
                'order_id' => $orderId,
                'campaign_id' => $validated['campaign_id'],
            ]);

            // Store order ID in session for verification (backup)
            session(['pending_order_id' => $orderId]);

            \Log::info('Payment Session - Session Stored', [
                'order_id' => $orderId,
                'session_key' => 'pending_order_id',
            ]);

            // Get checkout URL from response (check multiple possible response keys)
            $checkoutUrl = $response['checkout_url'] ?? $response['url'] ?? $response['payment_url'] ?? null;

            if (!$checkoutUrl) {
                \Log::warning('No checkout URL in response', ['response' => $response]);
                return response()->json(['error' => 'No checkout URL received from payment gateway'], 500);
            }

            return response()->json([
                'success' => true,
                'redirect_url' => $checkoutUrl,
            ]);
        } catch (\Exception $e) {
            \Log::error('Payment initialization failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Payment initialization failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verify payment after returning from Shurjopay
     */
    public function verifyPayment(Request $request)
    {
        try {
            // Log all incoming request data
            \Log::info('Verify Payment - Request Data', [
                'query_params' => $request->query(),
                'post_params' => $request->post(),
                'all_params' => $request->all(),
            ]);

            // Get order ID from query parameter, POST data, or session
            $orderId = $request->query('order_id') 
                ?? $request->input('order_id')
                ?? session('pending_order_id');

            \Log::info('Verify Payment - Starting', [
                'order_id' => $orderId,
                'has_query_param' => $request->has('order_id'),
                'has_post_param' => $request->input('order_id') !== null,
                'has_session' => session()->has('pending_order_id'),
            ]);

            if (!$orderId) {
                \Log::error('Verify Payment - No order ID found');
                return redirect()->route('public.donate')->with('error', 'Invalid payment response');
            }

            // Clear the session order ID
            session()->forget('pending_order_id');

            // Check if transaction exists
            $transaction = DB::table('transactions')
                ->where('order_id', $orderId)
                ->first();

            \Log::info('Verify Payment - Transaction Lookup', [
                'order_id' => $orderId,
                'transaction_found' => $transaction !== null,
                'transaction_status' => isset($transaction) ? $transaction->status : null,
            ]);

            if (!$transaction) {
                \Log::error('Verify Payment - Transaction not found', ['order_id' => $orderId]);
                // Maybe it's already in a different status, try to verify anyway
                return redirect()->route('public.donate')->with('error', 'Transaction not found');
            }

            // Verify payment with Shurjopay using official plugin
            \Log::info('Verify Payment - Calling ShurjoPay Verify');
            $verifyResponse = $this->shurjoPay->verifyPayment($orderId);

            \Log::info('Payment Verification Response', [
                'response' => $verifyResponse,
                'order_id' => $orderId,
                'transaction_status' => $transaction->status,
            ]);

            // Check if payment was successful (sp_code 1000 means success)
            $isSuccessful = false;
            if (is_array($verifyResponse)) {
                // Check if it's an array of responses
                if (isset($verifyResponse[0])) {
                    $firstResponse = $verifyResponse[0];
                    // Handle both array and object responses
                    if (is_array($firstResponse)) {
                        $isSuccessful = isset($firstResponse['sp_code']) && (int)$firstResponse['sp_code'] == 1000;
                    } elseif (is_object($firstResponse)) {
                        // stdClass object from ShurjoPay
                        $isSuccessful = isset($firstResponse->sp_code) && (int)$firstResponse->sp_code == 1000;
                    }
                }
            }

            \Log::info('Payment Verification Result', [
                'order_id' => $orderId,
                'is_successful' => $isSuccessful,
                'response_type' => gettype($verifyResponse),
                'first_response_type' => isset($verifyResponse[0]) ? gettype($verifyResponse[0]) : 'null',
            ]);

            if ($isSuccessful) {
                // Update transaction as completed
                DB::table('transactions')
                    ->where('order_id', $orderId)
                    ->update([
                        'status' => 'completed',
                        'payment_id' => $orderId, // Update payment_id with order_id for reference
                        'updated_at' => now(),
                    ]);

                \Log::info('Payment Verification - Transaction Updated', [
                    'order_id' => $orderId,
                    'new_status' => 'completed',
                ]);

                // Redirect to confirmation page instead of donation page
                \Log::info('Payment Verification - Redirecting to Confirmation', [
                    'order_id' => $orderId,
                    'route' => 'payment.confirmation',
                ]);
                return redirect()->route('payment.confirmation', ['orderId' => $orderId]);
            } else {
                // Update transaction as failed
                DB::table('transactions')
                    ->where('order_id', $orderId)
                    ->update([
                        'status' => 'failed',
                        'updated_at' => now(),
                    ]);

                \Log::warning('Payment Verification - Payment Failed', [
                    'order_id' => $orderId,
                ]);

                return redirect()->route('public.donate')->with('error', 'Payment failed or was cancelled');
            }
        } catch (\Exception $e) {
            \Log::error('Payment verification failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('public.donate')->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Show donation confirmation page
     */
    public function showConfirmation($orderId)
    {
        try {
            \Log::info('Show Confirmation - Starting', ['order_id' => $orderId]);

            // Get transaction details
            $transaction = DB::table('transactions')
                ->where('order_id', $orderId)
                ->first();

            if (!$transaction) {
                \Log::error('Show Confirmation - Transaction not found', ['order_id' => $orderId]);
                return redirect()->route('public.donate')->with('error', 'Transaction not found');
            }

            \Log::info('Show Confirmation - Transaction found', [
                'order_id' => $orderId,
                'status' => $transaction->status,
            ]);

            if ($transaction->status !== 'completed') {
                \Log::warning('Show Confirmation - Payment not confirmed', [
                    'order_id' => $orderId,
                    'status' => $transaction->status,
                ]);
                return redirect()->route('public.donate')->with('error', 'Payment not confirmed');
            }

            // Get campaign details
            $campaign = DB::table('fundraising')
                ->where('id', $transaction->campaign_id)
                ->first();

            \Log::info('Show Confirmation - Rendering view', [
                'order_id' => $orderId,
                'campaign_found' => $campaign !== null,
            ]);

            return view('public.donation-confirmation', [
                'transaction' => $transaction,
                'campaign' => $campaign,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error showing confirmation:', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('public.donate')->with('error', 'Error loading confirmation: ' . $e->getMessage());
        }
    }

    /**
     * Handle IPN (Instant Payment Notification) from Shurjopay
     */
    public function handleIPN(Request $request)
    {
        try {
            $orderId = $request->input('order_id');

            if (!$orderId) {
                return response()->json(['error' => 'Missing order_id'], 400);
            }

            // Verify payment with Shurjopay using official plugin (no token needed)
            $verifyResponse = $this->shurjoPay->verifyPayment($orderId);

            \Log::info('IPN Verification Response', ['order_id' => $orderId, 'response' => $verifyResponse]);

            // Check if payment was successful
            $isSuccessful = false;
            if (is_array($verifyResponse)) {
                if (isset($verifyResponse[0]) && is_array($verifyResponse[0])) {
                    $isSuccessful = isset($verifyResponse[0]['sp_code']) && $verifyResponse[0]['sp_code'] == 1000;
                } else {
                    $isSuccessful = isset($verifyResponse['sp_code']) && $verifyResponse['sp_code'] == 1000;
                }
            }

            if ($isSuccessful) {
                DB::table('transactions')
                    ->where('order_id', $orderId)
                    ->update([
                        'status' => 'completed',
                        'payment_id' => $orderId,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('transactions')
                    ->where('order_id', $orderId)
                    ->update([
                        'status' => 'failed',
                        'updated_at' => now(),
                    ]);
            }

            return response()->json(['message' => 'IPN processed'], 200);
        } catch (\Exception $e) {
            \Log::error('IPN processing failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'IPN processing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check payment status (for testing/manual verification)
     */
    public function checkPaymentStatus($orderId = null)
    {
        try {
            // If no order ID provided, try to get from session or request
            if (!$orderId) {
                $orderId = request()->query('order_id') ?? session('pending_order_id');
            }

            if (!$orderId) {
                return view('public.payment-status', [
                    'error' => 'No pending payment found. Please complete a donation first.',
                    'order_id' => null,
                ]);
            }

            // Get transaction from database
            $transaction = DB::table('transactions')
                ->where('order_id', $orderId)
                ->first();

            if (!$transaction) {
                return view('public.payment-status', [
                    'error' => 'Transaction not found',
                    'order_id' => $orderId,
                ]);
            }

            // If already completed, redirect to confirmation
            if ($transaction->status === 'completed') {
                return redirect()->route('payment.confirmation', ['orderId' => $orderId]);
            }

            // If failed, show error
            if ($transaction->status === 'failed') {
                return view('public.payment-status', [
                    'transaction' => $transaction,
                    'order_id' => $orderId,
                ]);
            }

            // If pending, try to verify with ShurjoPay
            try {
                $verifyResponse = $this->shurjoPay->verifyPayment($orderId);

                \Log::info('Payment Status Check', [
                    'order_id' => $orderId,
                    'response' => $verifyResponse,
                    'current_status' => $transaction->status,
                ]);

                // Check if payment was successful
                $isSuccessful = false;
                if (is_array($verifyResponse)) {
                    if (isset($verifyResponse[0]) && is_array($verifyResponse[0])) {
                        $isSuccessful = isset($verifyResponse[0]['sp_code']) && $verifyResponse[0]['sp_code'] == 1000;
                    } else {
                        $isSuccessful = isset($verifyResponse['sp_code']) && $verifyResponse['sp_code'] == 1000;
                    }
                }

                if ($isSuccessful) {
                    // Use the existing verifyPayment logic by creating a mock request
                    $request = new \Illuminate\Http\Request();
                    $request->merge(['order_id' => $orderId]);
                    return $this->verifyPayment($request);
                }
            } catch (\Exception $e) {
                \Log::error('Error checking payment status:', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                ]);
            }

            // Show current status
            return view('public.payment-status', [
                'transaction' => $transaction,
                'order_id' => $orderId,
                'message' => 'Payment status: ' . ucfirst($transaction->status),
            ]);
        } catch (\Exception $e) {
            \Log::error('Payment status check failed:', ['error' => $e->getMessage()]);
            return view('public.payment-status', [
                'error' => 'Error checking payment status: ' . $e->getMessage(),
                'order_id' => $orderId ?? null,
            ]);
        }
    }
}
