<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ShurjopayPlugin\Shurjopay;
use ShurjopayPlugin\ShurjopayConfig;
use ShurjopayPlugin\PaymentRequest;

class ShurjopayPaymentController extends Controller
{
    protected $shurjoPay;

    public function __construct()
    {
        // Initialize ShurjoPay directly from package
        $config = new ShurjopayConfig();
        $config->username = config('services.shurjopay.username');
        $config->password = config('services.shurjopay.password');
        $config->api_endpoint = config('services.shurjopay.api_endpoint');
        $config->callback_url = route('payment.shurjopay-verify', [], true);
        $config->order_prefix = config('services.shurjopay.prefix');
        $config->log_path = config('services.shurjopay.log_path');
        $config->ssl_verifypeer = env('CURLOPT_SSL_VERIFYPEER', 1);
        
        $this->shurjoPay = new Shurjopay($config);
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
     * Create payment request object from validated data
     */
    private function createPaymentRequest($validatedData, $localOrderId)
    {
        $request = new PaymentRequest();
        $request->currency = 'BDT';
        $request->amount = $validatedData['amount'];
        $request->discountAmount = 0;
        $request->discPercent = 0;
        $request->customerName = $validatedData['donor_name'];
        $request->customerPhone = $validatedData['donor_phone'];
        $request->customerEmail = $validatedData['donor_email'];
        $request->customerAddress = $validatedData['donor_phone'];
        $request->customerCity = 'Dhaka';
        $request->customerCountry = 'Bangladesh';
        $request->value1 = json_encode([
            'campaign_id' => $validatedData['campaign_id'],
            'local_order_id' => $localOrderId,
        ]);
        return $request;
    }

    /**
     * Get checkout URL from ShurjoPay API using reflection
     */
    private function getCheckoutUrl($paymentRequest)
    {
        $reflection = new \ReflectionClass($this->shurjoPay);
        
        // Authenticate
        $authMethod = $reflection->getMethod('authenticate');
        $authMethod->setAccessible(true);
        $authToken = $authMethod->invoke($this->shurjoPay);
        
        if (empty($authToken)) {
            throw new \Exception("Failed to obtain authentication token from ShurjoPay");
        }
        
        // Prepare transaction payload
        $prepareMethod = $reflection->getMethod('prepareTransactionPayload');
        $prepareMethod->setAccessible(true);
        $trxn_data = $prepareMethod->invoke($this->shurjoPay, $paymentRequest);
        
        // Make HTTP request
        $getHttpMethod = $reflection->getMethod('getHttpResponse');
        $getHttpMethod->setAccessible(true);
        
        $urlCheckoutProp = $reflection->getProperty('url_checkout');
        $urlCheckoutProp->setAccessible(true);
        $url_checkout = $urlCheckoutProp->getValue($this->shurjoPay);
        
        $header = [
            'Content-Type:application/json',
            'Authorization: Bearer ' . json_decode($trxn_data)->token
        ];
        
        return $getHttpMethod->invoke($this->shurjoPay, $url_checkout, 'POST', $trxn_data, $header);
    }

    /**
     * Check if payment verification response indicates success
     */
    private function isPaymentSuccessful($verifyResponse)
    {
        if (!is_array($verifyResponse)) {
            return false;
        }

        $firstResponse = $verifyResponse[0] ?? $verifyResponse;
        
        if (is_array($firstResponse)) {
            return isset($firstResponse['sp_code']) && (int)$firstResponse['sp_code'] == 1000;
        } elseif (is_object($firstResponse)) {
            return isset($firstResponse->sp_code) && (int)$firstResponse->sp_code == 1000;
        }
        
        return false;
    }

    /**
     * Get transaction by order ID
     */
    private function getTransaction($orderId)
    {
        return DB::table('transactions')->where('order_id', $orderId)->first();
    }

    /**
     * Update transaction status
     */
    private function updateTransactionStatus($orderId, $status, $paymentId = null)
    {
        $update = ['status' => $status, 'updated_at' => now()];
        if ($paymentId) {
            $update['payment_id'] = $paymentId;
        }
        return DB::table('transactions')->where('order_id', $orderId)->update($update);
    }

    /**
     * Store new transaction
     */
    private function storeTransaction($validatedData, $orderId)
    {
        DB::table('transactions')->insert([
            'campaign_id' => $validatedData['campaign_id'],
            'order_id' => $orderId,
            'amount' => $validatedData['amount'],
            'donor_name' => $validatedData['donor_name'],
            'donor_email' => $validatedData['donor_email'],
            'donor_phone' => $validatedData['donor_phone'],
            'status' => 'pending',
            'payment_method' => 'shurjopay',
            'payment_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
            $localOrderId = config('services.shurjopay.prefix') . '_' . uniqid();
            $paymentRequest = $this->createPaymentRequest($validated, $localOrderId);

            \Log::info('Creating payment with Shurjopay', ['amount' => $validated['amount']]);

            $response = $this->getCheckoutUrl($paymentRequest);
            $response = is_object($response) ? (array) $response : $response;

            if (empty($response) || isset($response['error'])) {
                return response()->json(['error' => $response['error'] ?? 'No response from gateway'], 500);
            }

            $orderId = $response['sp_order_id'] ?? $response['order_id'] ?? $localOrderId;
            $this->storeTransaction($validated, $orderId);
            session(['pending_order_id' => $orderId]);

            $checkoutUrl = $response['checkout_url'] ?? $response['url'] ?? $response['payment_url'] ?? null;
            if (!$checkoutUrl) {
                return response()->json(['error' => 'No checkout URL from gateway'], 500);
            }

            return response()->json(['success' => true, 'redirect_url' => $checkoutUrl]);
        } catch (\Exception $e) {
            \Log::error('Payment init failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verify payment after returning from Shurjopay
     */
    public function verifyPayment(Request $request)
    {
        try {
            $orderId = $request->query('order_id') ?? $request->input('order_id') ?? session('pending_order_id');

            if (!$orderId) {
                \Log::error('Verify Payment - No order ID found');
                return redirect()->route('public.donate')->with('error', 'Invalid payment response');
            }

            session()->forget('pending_order_id');
            $transaction = $this->getTransaction($orderId);

            if (!$transaction) {
                \Log::error('Verify Payment - Transaction not found', ['order_id' => $orderId]);
                return redirect()->route('public.donate')->with('error', 'Transaction not found');
            }

            $verifyResponse = $this->shurjoPay->verifyPayment($orderId);
            $isSuccessful = $this->isPaymentSuccessful($verifyResponse);

            if ($isSuccessful) {
                $this->updateTransactionStatus($orderId, 'completed', $orderId);
                return redirect()->route('payment.confirmation', ['orderId' => $orderId]);
            } else {
                $this->updateTransactionStatus($orderId, 'failed');
                return redirect()->route('public.donate')->with('error', 'Payment failed or was cancelled');
            }
        } catch (\Exception $e) {
            \Log::error('Payment verification failed', ['error' => $e->getMessage()]);
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

            $verifyResponse = $this->shurjoPay->verifyPayment($orderId);
            $isSuccessful = $this->isPaymentSuccessful($verifyResponse);

            $this->updateTransactionStatus($orderId, $isSuccessful ? 'completed' : 'failed', $isSuccessful ? $orderId : null);
            \Log::info('IPN Processed', ['order_id' => $orderId, 'success' => $isSuccessful]);

            return response()->json(['message' => 'IPN processed'], 200);
        } catch (\Exception $e) {
            \Log::error('IPN processing failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'IPN processing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Check payment status (for testing/manual verification)
     */
    public function checkPaymentStatus($orderId = null)
    {
        try {
            $orderId = $orderId ?? request()->query('order_id') ?? session('pending_order_id');

            if (!$orderId) {
                return view('public.payment-status', ['error' => 'No pending payment found']);
            }

            $transaction = $this->getTransaction($orderId);

            if (!$transaction) {
                return view('public.payment-status', ['error' => 'Transaction not found', 'order_id' => $orderId]);
            }

            if ($transaction->status === 'completed') {
                return redirect()->route('payment.confirmation', ['orderId' => $orderId]);
            }

            if ($transaction->status === 'failed') {
                return view('public.payment-status', ['transaction' => $transaction, 'order_id' => $orderId]);
            }

            // If pending, verify with ShurjoPay
            try {
                $verifyResponse = $this->shurjoPay->verifyPayment($orderId);
                if ($this->isPaymentSuccessful($verifyResponse)) {
                    $request = new \Illuminate\Http\Request();
                    $request->merge(['order_id' => $orderId]);
                    return $this->verifyPayment($request);
                }
            } catch (\Exception $e) {
                \Log::error('Error checking payment status', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            }

            return view('public.payment-status', ['transaction' => $transaction, 'order_id' => $orderId]);
        } catch (\Exception $e) {
            \Log::error('Payment status check failed', ['error' => $e->getMessage()]);
            return view('public.payment-status', ['error' => 'Error checking payment status: ' . $e->getMessage()]);
        }
    }
}
