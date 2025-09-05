<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PaymentApiService
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;
    private int $retryAttempts;
    private int $retryDelay;

    public function __construct()
    {
        $this->baseUrl = config('services.payment.base_url');
        $this->apiKey = config('services.payment.api_key');
        $this->timeout = config('services.payment.timeout', 30);
        $this->retryAttempts = config('services.payment.retry_attempts', 3);
        $this->retryDelay = config('services.payment.retry_delay', 1000);
    }

    /**
     * Process payment
     */
    public function processPayment(array $paymentData): array
    {
        $cacheKey = $this->generateCacheKey('payment', $paymentData);
        
        return Cache::remember($cacheKey, 300, function () use ($paymentData) {
            return $this->makeRequest('POST', '/payments', $paymentData);
        });
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentId): array
    {
        $cacheKey = $this->generateCacheKey('payment_status', ['id' => $paymentId]);
        
        return Cache::remember($cacheKey, 60, function () use ($paymentId) {
            return $this->makeRequest('GET', "/payments/{$paymentId}");
        });
    }

    /**
     * Refund payment
     */
    public function refundPayment(string $paymentId, float $amount): array
    {
        $data = [
            'payment_id' => $paymentId,
            'amount' => $amount,
            'reason' => 'Customer request'
        ];
        
        return $this->makeRequest('POST', '/refunds', $data);
    }

    /**
     * Get payment methods
     */
    public function getPaymentMethods(): array
    {
        $cacheKey = 'payment_methods';
        
        return Cache::remember($cacheKey, 3600, function () {
            return $this->makeRequest('GET', '/payment-methods');
        });
    }

    /**
     * Create customer
     */
    public function createCustomer(array $customerData): array
    {
        return $this->makeRequest('POST', '/customers', $customerData);
    }

    /**
     * Update customer
     */
    public function updateCustomer(string $customerId, array $customerData): array
    {
        return $this->makeRequest('PUT', "/customers/{$customerId}", $customerData);
    }

    /**
     * Get customer
     */
    public function getCustomer(string $customerId): array
    {
        $cacheKey = $this->generateCacheKey('customer', ['id' => $customerId]);
        
        return Cache::remember($cacheKey, 1800, function () use ($customerId) {
            return $this->makeRequest('GET', "/customers/{$customerId}");
        });
    }

    /**
     * Make HTTP request with retry logic
     */
    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->baseUrl . $endpoint;
        $attempt = 0;
        
        while ($attempt < $this->retryAttempts) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'X-Request-ID' => uniqid()
                    ])
                    ->$method($url, $data);
                
                if ($response->successful()) {
                    Log::info('API request successful', [
                        'method' => $method,
                        'endpoint' => $endpoint,
                        'status' => $response->status(),
                        'attempt' => $attempt + 1
                    ]);
                    
                    return $response->json();
                }
                
                if ($response->status() >= 500) {
                    throw new \Exception('Server error: ' . $response->status());
                }
                
                throw new \Exception('Client error: ' . $response->status());
                
            } catch (\Exception $e) {
                $attempt++;
                
                Log::warning('API request failed', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempt >= $this->retryAttempts) {
                    throw new \Exception('API request failed after ' . $this->retryAttempts . ' attempts: ' . $e->getMessage());
                }
                
                sleep($this->retryDelay / 1000);
            }
        }
        
        throw new \Exception('API request failed');
    }

    /**
     * Generate cache key
     */
    private function generateCacheKey(string $prefix, array $params = []): string
    {
        $key = $prefix;
        
        if (!empty($params)) {
            $key .= ':' . md5(serialize($params));
        }
        
        return $key;
    }

    /**
     * Clear cache
     */
    public function clearCache(string $pattern = null): void
    {
        if ($pattern) {
            Cache::forget($pattern);
        } else {
            Cache::flush();
        }
        
        Log::info('Payment API cache cleared', ['pattern' => $pattern]);
    }

    /**
     * Get API statistics
     */
    public function getApiStats(): array
    {
        return [
            'base_url' => $this->baseUrl,
            'timeout' => $this->timeout,
            'retry_attempts' => $this->retryAttempts,
            'retry_delay' => $this->retryDelay,
            'cache_enabled' => true,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }
}
