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
            $orderId = config('services.shurjopay.prefix') . '_' . uniqid();

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
                    'order_id' => $orderId,
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

            // Store transaction record
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
            $orderId = $request->query('order_id');

            if (!$orderId) {
                return redirect()->route('public.donate')->with('error', 'Invalid payment response');
            }

            // Verify payment with Shurjopay using official plugin (no token needed)
            $verifyResponse = $this->shurjoPay->verifyPayment($orderId);

            // Update transaction status
            $transaction = DB::table('transactions')
                ->where('order_id', $orderId)
                ->first();

            if (!$transaction) {
                return redirect()->route('public.donate')->with('error', 'Transaction not found');
            }

            \Log::info('Payment Verification Response', ['response' => $verifyResponse, 'order_id' => $orderId]);

            // Check if payment was successful (sp_code 1000 means success)
            $isSuccessful = false;
            if (is_array($verifyResponse)) {
                // Check if it's an array of responses
                if (isset($verifyResponse[0]) && is_array($verifyResponse[0])) {
                    $isSuccessful = isset($verifyResponse[0]['sp_code']) && $verifyResponse[0]['sp_code'] == 1000;
                } else {
                    // Single response object
                    $isSuccessful = isset($verifyResponse['sp_code']) && $verifyResponse['sp_code'] == 1000;
                }
            }

            if ($isSuccessful) {
                // Update transaction as completed
                DB::table('transactions')
                    ->where('order_id', $orderId)
                    ->update([
                        'status' => 'completed',
                        'payment_id' => $orderId // Update payment_id with order_id for reference
                    ]);

                // Get campaign and donor info from transaction
                $transaction = DB::table('transactions')
                    ->where('order_id', $orderId)
                    ->first();

                    

                // Create fundraising donation record
                DB::table('fundraising')->insert([
                    'disaster_id' => DB::table('fundraising')
                        ->where('id', $transaction->campaign_id)
                        ->value('disaster_id'),
                    'person_id' => DB::table('people')
                        ->where('email', $transaction->donor_email)
                        ->value('id') ?? DB::table('people')->insertGetId([
                            'name' => $transaction->donor_name,
                            'email' => $transaction->donor_email,
                            'phone' => $transaction->donor_phone,
                            'created_at' => now(),
                        ]),
                    'amount' => $transaction->amount,
                    'role' => 'donor',
                    'status' => 'active',
                    'created_at' => now(),
                ]);

                return redirect()->route('public.donate')->with('success', 'Donation received successfully! Thank you for your support.');
            } else {
                // Update transaction as failed
                DB::table('transactions')
                    ->where('order_id', $orderId)
                    ->update(['status' => 'failed']);

                return redirect()->route('public.donate')->with('error', 'Payment failed or was cancelled');
            }
        } catch (\Exception $e) {
            \Log::error('Payment verification failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->route('public.donate')->with('error', 'Payment verification failed: ' . $e->getMessage());
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
                        'payment_id' => $orderId
                    ]);
            } else {
                DB::table('transactions')
                    ->where('order_id', $orderId)
                    ->update(['status' => 'failed']);
            }

            return response()->json(['message' => 'IPN processed'], 200);
        } catch (\Exception $e) {
            \Log::error('IPN processing failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'IPN processing failed: ' . $e->getMessage()], 500);
        }
    }
}
