<?php

namespace App\Services;

use App\Core\Database;
use Exception;

class RazorpayService
{
    private Database $db;
    private string $keyId;
    private string $keySecret;
    private string $webhookSecret;
    private string $mode;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadSettings();
    }
    
    private function loadSettings(): void
    {
        $settings = $this->db->fetchAll("SELECT * FROM settings WHERE `group` = 'payment'");
        
        foreach ($settings as $setting) {
            switch ($setting['key']) {
                case 'razorpay_key_id':
                    $this->keyId = $setting['value'] ?? '';
                    break;
                case 'razorpay_key_secret':
                    $this->keySecret = $setting['value'] ?? '';
                    break;
                case 'razorpay_webhook_secret':
                    $this->webhookSecret = $setting['value'] ?? '';
                    break;
                case 'payment_mode':
                    $this->mode = $setting['value'] ?? 'test';
                    break;
            }
        }
    }
    
    /**
     * Create Razorpay Order
     */
    public function createOrder(array $data): array
    {
        $amount = (float) $data['amount'];
        $currency = $data['currency'] ?? 'INR';
        $receipt = $data['receipt'] ?? 'rcpt_' . time();
        
        $orderData = [
            'amount' => $amount * 100, // Convert to paise
            'currency' => $currency,
            'receipt' => $receipt,
            'notes' => $data['notes'] ?? []
        ];
        
        $response = $this->makeApiCall('orders', 'POST', $orderData);
        
        return $response;
    }
    
    /**
     * Verify Payment Signature
     */
    public function verifyPaymentSignature(array $data): bool
    {
        $orderId = $data['razorpay_order_id'] ?? '';
        $paymentId = $data['razorpay_payment_id'] ?? '';
        $signature = $data['razorpay_signature'] ?? '';
        
        $generatedSignature = hash_hmac(
            'sha256',
            $orderId . '|' . $paymentId,
            $this->keySecret
        );
        
        return hash_equals($generatedSignature, $signature);
    }
    
    /**
     * Verify Webhook Signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $generatedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($generatedSignature, $signature);
    }
    
    /**
     * Fetch Payment Details
     */
    public function getPayment(string $paymentId): array
    {
        return $this->makeApiCall("payments/$paymentId", 'GET');
    }
    
    /**
     * Capture Payment
     */
    public function capturePayment(string $paymentId, float $amount): array
    {
        $data = ['amount' => $amount * 100]; // Convert to paise
        return $this->makeApiCall("payments/$paymentId/capture", 'POST', $data);
    }
    
    /**
     * Refund Payment
     */
    public function refundPayment(string $paymentId, float $amount = null): array
    {
        $data = [];
        if ($amount !== null) {
            $data['amount'] = $amount * 100; // Convert to paise
        }
        
        return $this->makeApiCall("payments/$paymentId/refund", 'POST', $data);
    }
    
    /**
     * Get Refund Details
     */
    public function getRefund(string $refundId): array
    {
        return $this->makeApiCall("refunds/$refundId", 'GET');
    }
    
    /**
     * Make API Call to Razorpay
     */
    private function makeApiCall(string $endpoint, string $method = 'GET', array $data = []): array
    {
        $url = "https://api.razorpay.com/v1/$endpoint";
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->keyId . ':' . $this->keySecret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Razorpay API Error: $error");
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMsg = $result['error']['description'] ?? 'Unknown error';
            throw new Exception("Razorpay Error: $errorMsg");
        }
        
        return $result;
    }
    
    /**
     * Get Razorpay Key ID for frontend
     */
    public function getKeyId(): string
    {
        return $this->keyId;
    }
    
    /**
     * Get Payment Mode
     */
    public function getMode(): string
    {
        return $this->mode;
    }
    
    /**
     * Check if Razorpay is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->keyId) && !empty($this->keySecret);
    }
}
