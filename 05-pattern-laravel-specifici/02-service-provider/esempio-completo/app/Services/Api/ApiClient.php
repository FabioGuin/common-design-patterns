<?php

namespace App\Services\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ApiClient
{
    protected Client $client;
    protected string $baseUrl;
    protected string $apiKey;
    protected int $timeout;
    protected int $retryAttempts;

    public function __construct(
        string $baseUrl,
        string $apiKey,
        int $timeout = 30,
        int $retryAttempts = 3
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->timeout = $timeout;
        $this->retryAttempts = $retryAttempts;

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Esegui richiesta GET
     */
    public function get(string $endpoint, array $params = []): ApiResponse
    {
        return $this->makeRequest('GET', $endpoint, [
            'query' => $params,
        ]);
    }

    /**
     * Esegui richiesta POST
     */
    public function post(string $endpoint, array $data = []): ApiResponse
    {
        return $this->makeRequest('POST', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Esegui richiesta PUT
     */
    public function put(string $endpoint, array $data = []): ApiResponse
    {
        return $this->makeRequest('PUT', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Esegui richiesta PATCH
     */
    public function patch(string $endpoint, array $data = []): ApiResponse
    {
        return $this->makeRequest('PATCH', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Esegui richiesta DELETE
     */
    public function delete(string $endpoint): ApiResponse
    {
        return $this->makeRequest('DELETE', $endpoint);
    }

    /**
     * Esegui richiesta con retry
     */
    protected function makeRequest(string $method, string $endpoint, array $options = []): ApiResponse
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $response = $this->client->request($method, $endpoint, $options);
                
                return new ApiResponse(
                    $response->getStatusCode(),
                    json_decode($response->getBody()->getContents(), true),
                    $response->getHeaders()
                );

            } catch (RequestException $e) {
                $lastException = $e;
                $attempt++;

                // Log dell'errore
                Log::warning("API Request failed (attempt {$attempt}): " . $e->getMessage(), [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'status_code' => $e->getCode(),
                ]);

                // Se non è l'ultimo tentativo, aspetta prima di riprovare
                if ($attempt < $this->retryAttempts) {
                    sleep(pow(2, $attempt)); // Exponential backoff
                }
            }
        }

        // Se tutti i tentativi sono falliti, lancia l'ultima eccezione
        throw new \Exception(
            "API request failed after {$this->retryAttempts} attempts: " . 
            ($lastException ? $lastException->getMessage() : 'Unknown error')
        );
    }

    /**
     * Testa la connessione API
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->get('/health');
            return $response->isSuccessful();
        } catch (\Exception $e) {
            Log::error('API connection test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ottieni informazioni sull'API
     */
    public function getApiInfo(): array
    {
        try {
            $response = $this->get('/info');
            return $response->getData();
        } catch (\Exception $e) {
            Log::error('Failed to get API info: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Ottieni rate limit info
     */
    public function getRateLimitInfo(): array
    {
        try {
            $response = $this->get('/rate-limit');
            return $response->getData();
        } catch (\Exception $e) {
            Log::error('Failed to get rate limit info: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Imposta header personalizzati
     */
    public function setHeaders(array $headers): void
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'headers' => array_merge([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ], $headers),
        ]);
    }

    /**
     * Ottieni configurazione corrente
     */
    public function getConfig(): array
    {
        return [
            'base_url' => $this->baseUrl,
            'timeout' => $this->timeout,
            'retry_attempts' => $this->retryAttempts,
            'has_api_key' => !empty($this->apiKey),
        ];
    }
}
