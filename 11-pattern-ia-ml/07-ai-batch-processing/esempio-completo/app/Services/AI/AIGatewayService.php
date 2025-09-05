<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AIGatewayService
{
    private array $providers = [
        'openai' => [
            'base_url' => 'https://api.openai.com/v1',
            'batch_endpoint' => '/chat/completions',
            'max_batch_size' => 1000,
        ],
        'claude' => [
            'base_url' => 'https://api.anthropic.com/v1',
            'batch_endpoint' => '/messages',
            'max_batch_size' => 500,
        ],
        'gemini' => [
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'batch_endpoint' => '/models/{model}:generateContent',
            'max_batch_size' => 1000,
        ],
    ];

    /**
     * Processa un batch di richieste
     */
    public function processBatch(array $config): array
    {
        $provider = $config['provider'];
        $model = $config['model'];
        $requests = $config['requests'];

        if (!isset($this->providers[$provider])) {
            throw new Exception("Unsupported provider: {$provider}");
        }

        $providerConfig = $this->providers[$provider];
        
        if (count($requests) > $providerConfig['max_batch_size']) {
            throw new Exception("Batch size exceeds maximum for provider {$provider}");
        }

        Log::info('Processing AI batch', [
            'provider' => $provider,
            'model' => $model,
            'request_count' => count($requests),
        ]);

        try {
            $responses = $this->sendBatchRequest($provider, $model, $requests);
            return $this->formatResponses($responses, $provider);
        } catch (Exception $e) {
            Log::error('AI batch processing failed', [
                'provider' => $provider,
                'model' => $model,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Invia la richiesta batch al provider
     */
    private function sendBatchRequest(string $provider, string $model, array $requests): array
    {
        $providerConfig = $this->providers[$provider];
        
        switch ($provider) {
            case 'openai':
                return $this->sendOpenAIRequest($model, $requests);
            case 'claude':
                return $this->sendClaudeRequest($model, $requests);
            case 'gemini':
                return $this->sendGeminiRequest($model, $requests);
            default:
                throw new Exception("Provider not implemented: {$provider}");
        }
    }

    /**
     * Invia richiesta a OpenAI
     */
    private function sendOpenAIRequest(string $model, array $requests): array
    {
        $apiKey = config('ai.providers.openai.api_key');
        
        if (!$apiKey) {
            throw new Exception('OpenAI API key not configured');
        }

        $responses = [];
        
        // OpenAI non supporta batch nativi, quindi simuliamo
        foreach ($requests as $index => $request) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $request]
                    ],
                    'max_tokens' => 1000,
                    'temperature' => 0.7,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $responses[] = [
                        'success' => true,
                        'output' => $data['choices'][0]['message']['content'] ?? '',
                        'usage' => $data['usage'] ?? [],
                    ];
                } else {
                    $responses[] = [
                        'success' => false,
                        'error' => $response->json('error.message', 'Unknown error'),
                    ];
                }
            } catch (Exception $e) {
                $responses[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $responses;
    }

    /**
     * Invia richiesta a Claude
     */
    private function sendClaudeRequest(string $model, array $requests): array
    {
        $apiKey = config('ai.providers.claude.api_key');
        
        if (!$apiKey) {
            throw new Exception('Claude API key not configured');
        }

        $responses = [];
        
        foreach ($requests as $index => $request) {
            try {
                $response = Http::withHeaders([
                    'x-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                    'anthropic-version' => '2023-06-01',
                ])->post('https://api.anthropic.com/v1/messages', [
                    'model' => $model,
                    'max_tokens' => 1000,
                    'messages' => [
                        ['role' => 'user', 'content' => $request]
                    ],
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $responses[] = [
                        'success' => true,
                        'output' => $data['content'][0]['text'] ?? '',
                        'usage' => $data['usage'] ?? [],
                    ];
                } else {
                    $responses[] = [
                        'success' => false,
                        'error' => $response->json('error.message', 'Unknown error'),
                    ];
                }
            } catch (Exception $e) {
                $responses[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $responses;
    }

    /**
     * Invia richiesta a Gemini
     */
    private function sendGeminiRequest(string $model, array $requests): array
    {
        $apiKey = config('ai.providers.gemini.api_key');
        
        if (!$apiKey) {
            throw new Exception('Gemini API key not configured');
        }

        $responses = [];
        
        foreach ($requests as $index => $request) {
            try {
                $response = Http::get("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'key' => $apiKey,
                ])->withBody(json_encode([
                    'contents' => [
                        ['parts' => [['text' => $request]]]
                    ],
                    'generationConfig' => [
                        'maxOutputTokens' => 1000,
                        'temperature' => 0.7,
                    ],
                ]), 'application/json');

                if ($response->successful()) {
                    $data = $response->json();
                    $responses[] = [
                        'success' => true,
                        'output' => $data['candidates'][0]['content']['parts'][0]['text'] ?? '',
                        'usage' => $data['usageMetadata'] ?? [],
                    ];
                } else {
                    $responses[] = [
                        'success' => false,
                        'error' => $response->json('error.message', 'Unknown error'),
                    ];
                }
            } catch (Exception $e) {
                $responses[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $responses;
    }

    /**
     * Formatta le risposte in un formato standard
     */
    private function formatResponses(array $responses, string $provider): array
    {
        return array_map(function ($response) use ($provider) {
            return [
                'success' => $response['success'],
                'output' => $response['output'] ?? '',
                'error' => $response['error'] ?? null,
                'provider' => $provider,
                'usage' => $response['usage'] ?? [],
                'processing_time_ms' => $response['processing_time_ms'] ?? null,
            ];
        }, $responses);
    }

    /**
     * Ottiene i provider disponibili
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }

    /**
     * Ottiene la configurazione di un provider
     */
    public function getProviderConfig(string $provider): array
    {
        if (!isset($this->providers[$provider])) {
            throw new Exception("Provider not found: {$provider}");
        }

        return $this->providers[$provider];
    }
}
