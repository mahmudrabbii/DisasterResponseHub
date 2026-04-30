<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShurjoPayService
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.shurjopay.base_url');
    }

    // 1. Get Token
    public function getToken()
    {
        $response = Http::withoutVerifying()
            ->post($this->baseUrl . '/get_token', [
                'username' => config('services.shurjopay.username'),
                'password' => config('services.shurjopay.password'),
            ]);

        return $response->json();
    }

    // 2. Create Payment
    public function createPayment($data, $token)
    {
        // Add token to the data array instead of using Bearer token
        $data['token'] = $token;
        
        $response = Http::withoutVerifying()
            ->asForm()
            ->post($this->baseUrl . '/secret-pay', $data);

        \Log::info('Create Payment Response Status:', ['status' => $response->status()]);
        \Log::info('Create Payment Response Body:', ['body' => $response->body()]);

        return $response->json();
    }

    // 3. Verify Payment
    public function verifyPayment($order_id, $token)
    {
        $response = Http::withoutVerifying()
            ->asForm()
            ->post($this->baseUrl . '/verification', [
                'token' => $token,
                'order_id' => $order_id
            ]);

        \Log::info('Verify Payment Response Status:', ['status' => $response->status()]);
        \Log::info('Verify Payment Response Body:', ['body' => $response->body()]);

        return $response->json();
    }
}
