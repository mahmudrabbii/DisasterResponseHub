<?php
namespace App\Services;

use ShurjopayPlugin\PaymentRequest;
use ShurjopayPlugin\Shurjopay;
use ShurjopayPlugin\ShurjopayConfig;
use Illuminate\Support\Facades\Log;

class ShurjoPayService
{
    private $shurjopay;

    public function __construct()
    {
        // Create ShurjopayConfig with configuration from Laravel config
        $config = new ShurjopayConfig();
        $config->username = config('services.shurjopay.username');
        $config->password = config('services.shurjopay.password');
        $config->api_endpoint = config('services.shurjopay.api_endpoint');
        $config->callback_url = config('services.shurjopay.callback_url');
        $config->order_prefix = config('services.shurjopay.prefix');
        $config->log_path = config('services.shurjopay.log_path');
        $config->ssl_verifypeer = env('CURLOPT_SSL_VERIFYPEER', 1); // 1 for SSL verification, 0 to disable
        
        Log::info('ShurjoPay Config Initialized', [
            'api_endpoint' => $config->api_endpoint,
            'username' => $config->username,
            'prefix' => $config->order_prefix,
            'log_path' => $config->log_path,
        ]);
        
        // Initialize Shurjopay with config
        $this->shurjopay = new Shurjopay($config);
    }

    /**
     * Authenticate and get payment token
     * 
     * @return array
     */
    public function authenticate()
    {
        try {
            $response = $this->shurjopay->authenticate();
            Log::info('ShurjoPay Authentication Response', ['response' => $response]);
            return $response;
        } catch (\Exception $e) {
            Log::error('ShurjoPay Authentication Error', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Create payment request using official plugin
     * 
     * @param array $paymentData Payment details
     * @return object The response object from ShurjoPay API
     * @throws \Exception
     */
    public function createPayment(array $paymentData)
    {
        try {
            // First, ensure we have a valid token
            Log::info('Creating payment - checking token');
            
            // Create payment request object
            $request = new PaymentRequest();
            
            $request->currency = $paymentData['currency'] ?? 'BDT';
            $request->amount = $paymentData['amount'];
            $request->discountAmount = $paymentData['discount_amount'] ?? 0;
            $request->discPercent = $paymentData['disc_percent'] ?? 0;
            $request->customerName = $paymentData['customer_name'];
            $request->customerPhone = $paymentData['customer_phone'];
            $request->customerEmail = $paymentData['customer_email'];
            $request->customerAddress = $paymentData['customer_address'] ?? '';
            $request->customerCity = $paymentData['customer_city'] ?? 'Dhaka';
            $request->customerState = $paymentData['customer_state'] ?? '';
            $request->customerPostcode = $paymentData['customer_postcode'] ?? '';
            $request->customerCountry = $paymentData['customer_country'] ?? 'Bangladesh';
            
            // Optional shipping details
            if (isset($paymentData['shipping_address'])) {
                $request->shippingAddress = $paymentData['shipping_address'];
            }
            if (isset($paymentData['shipping_city'])) {
                $request->shippingCity = $paymentData['shipping_city'];
            }
            if (isset($paymentData['shipping_country'])) {
                $request->shippingCountry = $paymentData['shipping_country'];
            }
            if (isset($paymentData['shipping_phone_number'])) {
                $request->shippingPhoneNumber = $paymentData['shipping_phone_number'];
            }
            if (isset($paymentData['received_person_name'])) {
                $request->receivedPersonName = $paymentData['received_person_name'];
            }
            
            // Custom data fields (optional)
            if (isset($paymentData['value1'])) {
                $request->value1 = $paymentData['value1'];
            }
            if (isset($paymentData['value2'])) {
                $request->value2 = $paymentData['value2'];
            }
            if (isset($paymentData['value3'])) {
                $request->value3 = $paymentData['value3'];
            }
            if (isset($paymentData['value4'])) {
                $request->value4 = $paymentData['value4'];
            }

            Log::info('ShurjoPay PaymentRequest prepared', [
                'amount' => $request->amount,
                'currency' => $request->currency,
                'customer_name' => $request->customerName,
            ]);

            // Get direct API response without using makePayment (which does redirect)
            return $this->makePaymentRequest($request);
            
        } catch (\Exception $e) {
            Log::error('ShurjoPay Payment Creation Error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    /**
     * Make payment request directly to ShurjoPay API
     * Avoids the plugin's header redirect by using reflection to call internal methods
     * 
     * @param PaymentRequest $request
     * @return object
     * @throws \Exception
     */
    private function makePaymentRequest(PaymentRequest $request)
    {
        try {
            // Use reflection to access and call private methods
            $reflection = new \ReflectionClass($this->shurjopay);
            
            // Step 1: Authenticate first
            Log::info('Step 1: Authenticating with ShurjoPay');
            $authMethod = $reflection->getMethod('authenticate');
            $authMethod->setAccessible(true);
            $authToken = $authMethod->invoke($this->shurjopay);
            
            Log::info('Authentication result', ['token_received' => !empty($authToken)]);
            
            if (empty($authToken)) {
                throw new \Exception("Failed to obtain authentication token from ShurjoPay");
            }
            
            // Step 2: Prepare transaction payload
            Log::info('Step 2: Preparing transaction payload');
            $prepareMethod = $reflection->getMethod('prepareTransactionPayload');
            $prepareMethod->setAccessible(true);
            $trxn_data = $prepareMethod->invoke($this->shurjopay, $request);
            
            Log::info('Transaction payload prepared');
            
            // Step 3: Make HTTP request to checkout endpoint
            Log::info('Step 3: Making HTTP request to checkout endpoint');
            $getHttpMethod = $reflection->getMethod('getHttpResponse');
            $getHttpMethod->setAccessible(true);
            
            // Get the checkout URL
            $urlCheckoutProp = $reflection->getProperty('url_checkout');
            $urlCheckoutProp->setAccessible(true);
            $url_checkout = $urlCheckoutProp->getValue($this->shurjopay);
            
            // Prepare headers
            $header = array(
                'Content-Type:application/json',
                'Authorization: Bearer ' . json_decode($trxn_data)->token
            );
            
            // Make the request
            $response = $getHttpMethod->invoke($this->shurjopay, $url_checkout, 'POST', $trxn_data, $header);
            
            Log::info('Payment response received', [
                'has_checkout_url' => isset($response->checkout_url),
                'response_object' => json_encode($response)
            ]);
            
            // Return the response without the plugin's redirect
            return $response;
            
        } catch (\ReflectionException $e) {
            Log::error('Reflection error', ['error' => $e->getMessage()]);
            throw new \Exception("Error accessing ShurjoPay plugin methods: " . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Payment request error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    /**
     * Verify payment status
     * 
     * @param string $orderId ShurjoPay order ID
     * @return array
     */
    public function verifyPayment($orderId)
    {
        try {
            $response = $this->shurjopay->verifyPayment($orderId);
            
            Log::info('ShurjoPay Payment Verification Response', [
                'order_id' => $orderId,
                'response' => $response
            ]);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('ShurjoPay Payment Verification Error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get Shurjopay instance for advanced usage
     * 
     * @return Shurjopay
     */
    public function getInstance()
    {
        return $this->shurjopay;
    }
}
