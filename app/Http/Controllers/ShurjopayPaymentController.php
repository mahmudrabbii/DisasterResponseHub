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
            // Get token from Shurjopay
            $tokenData = $this->shurjoPay->getToken();

            \Log::info('Shurjopay Token Response:', $tokenData);

            if (!isset($tokenData['token'])) {
                return response()->json(['error' => 'Failed to generate payment token. Response: ' . json_encode($tokenData)], 500);
            }

            $token = $tokenData['token'];
            $orderId = 'ORD-' . uniqid();

            // Prepare payment data
            $paymentData = [
                'prefix' => config('services.shurjopay.prefix'),
                'return_url' => route('payment.shurjopay-verify'),
                'cancel_url' => route('public.donate'),
                'store_id' => $tokenData['store_id'] ?? config('services.shurjopay.store_id'),
                'amount' => $validated['amount'],
                'order_id' => $orderId,
                'currency' => 'BDT',
                'customer_name' => $validated['donor_name'],
                'customer_address' => $validated['donor_phone'],
                'customer_email' => $validated['donor_email'],
                'customer_phone' => $validated['donor_phone'],
                'customer_city' => 'Dhaka',
                'client_ip' => $request->ip(),
            ];

            \Log::info('Payment Data:', $paymentData);

            // Create payment session with Shurjopay
            $response = $this->shurjoPay->createPayment($paymentData, $token);

            \Log::info('Shurjopay Payment Response:', $response);

            if (!isset($response['checkout_url']) && !isset($response['url'])) {
                return response()->json(['error' => 'Failed to create payment session. Response: ' . json_encode($response)], 500);
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
                'created_at' => now(),
            ]);

            // Get checkout URL from response
            $checkoutUrl = $response['checkout_url'] ?? $response['url'] ?? null;

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

            // Get token from Shurjopay
            $tokenData = $this->shurjoPay->getToken();
            $token = $tokenData['token'];

            // Verify payment with Shurjopay
            $verifyResponse = $this->shurjoPay->verifyPayment($orderId, $token);

            // Update transaction status
            $transaction = DB::table('transactions')
                ->where('order_id', $orderId)
                ->first();

            if (!$transaction) {
                return redirect()->route('public.donate')->with('error', 'Transaction not found');
            }

            // Check if payment was successful (sp_code 1000 means success)
            if (isset($verifyResponse[0]['sp_code']) && $verifyResponse[0]['sp_code'] == 1000) {
                // Update transaction as completed
                DB::table('transactions')
                    ->where('order_id', $orderId)
                    ->update(['status' => 'completed']);

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
            $status = $request->input('status');

            // Get token from Shurjopay
            $tokenData = $this->shurjoPay->getToken();
            $token = $tokenData['token'];

            // Verify payment with Shurjopay
            $verifyResponse = $this->shurjoPay->verifyPayment($orderId, $token);

            // Check if payment was successful
            if (isset($verifyResponse[0]['sp_code']) && $verifyResponse[0]['sp_code'] == 1000) {
                DB::table('transactions')
                    ->where('order_id', $orderId)
                    ->update(['status' => 'completed']);
            } else {
                DB::table('transactions')
                    ->where('order_id', $orderId)
                    ->update(['status' => 'failed']);
            }

            return response()->json(['message' => 'IPN processed'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'IPN processing failed'], 500);
        }
    }
}
