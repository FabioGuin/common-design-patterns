<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AIModelInterface
{
    private string $name;
    private array $config;
    private array $modelConfig;
    private bool $available;
    private array $performanceStats;

    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->config = $config;
        $this->modelConfig = config('ai_models.models.' . $name, []);
        $this->available = true;
        $this->performanceStats = [
            'total_requests' => 0,
            'successful_requests' => 0,
            'total_duration' => 0,
            'average_response_time' => 0,
            'success_rate' => 0
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProvider(): string
    {
        return 'gemini';
    }

    public function getDescription(): string
    {
        return $this->modelConfig['description'] ?? 'Gemini Model';
    }

    public function getCapabilities(): array
    {
        return $this->modelConfig['capabilities'] ?? [];
    }

    public function getCostPerToken(): float
    {
        return $this->modelConfig['cost_per_token'] ?? 0.0;
    }

    public function getMaxTokens(): int
    {
        return $this->modelConfig['max_tokens'] ?? 4096;
    }

    public function getContextWindow(): int
    {
        return $this->modelConfig['context_window'] ?? 4096;
    }

    public function getPriority(): int
    {
        return $this->modelConfig['priority'] ?? 5;
    }

    public function isAvailable(): bool
    {
        return $this->available && $this->isConfigured();
    }

    public function setAvailable(bool $available): void
    {
        $this->available = $available;
    }

    public function getAverageResponseTime(): float
    {
        return $this->performanceStats['average_response_time'];
    }

    public function getSuccessRate(): float
    {
        return $this->performanceStats['success_rate'];
    }

    public function getTags(): array
    {
        return $this->modelConfig['tags'] ?? [];
    }

    public function generateText(string $prompt, array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $response = $this->makeRequest('generateContent', [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => $options['max_tokens'] ?? $this->getMaxTokens(),
                    'temperature' => $options['temperature'] ?? 0.7,
                    'topP' => $options['top_p'] ?? 1.0,
                    'topK' => $options['top_k'] ?? 40
                ]
            ]);

            $duration = microtime(true) - $startTime;
            $this->updatePerformance($duration, true);

            return [
                'text' => $response['candidates'][0]['content']['parts'][0]['text'] ?? '',
                'tokens_used' => $response['usageMetadata']['totalTokenCount'] ?? 0,
                'model' => $this->name,
                'provider' => $this->getProvider(),
                'duration' => $duration,
                'cost' => $this->calculateCost($response['usageMetadata']['totalTokenCount'] ?? 0)
            ];

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            $this->updatePerformance($duration, false);
            
            Log::error('Gemini API Error', [
                'model' => $this->name,
                'error' => $e->getMessage(),
                'duration' => $duration
            ]);
            
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): array
    {
        if (!$this->supportsCapability('image_generation')) {
            throw new \Exception('Modello non supporta la generazione di immagini');
        }

        $startTime = microtime(true);
        
        try {
            $response = $this->makeRequest('generateContent', [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => $options['max_tokens'] ?? $this->getMaxTokens(),
                    'temperature' => $options['temperature'] ?? 0.7
                ]
            ]);

            $duration = microtime(true) - $startTime;
            $this->updatePerformance($duration, true);

            return [
                'image_url' => $response['candidates'][0]['content']['parts'][0]['text'] ?? '',
                'model' => $this->name,
                'provider' => $this->getProvider(),
                'duration' => $duration,
                'cost' => $this->calculateCost($response['usageMetadata']['totalTokenCount'] ?? 0)
            ];

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            $this->updatePerformance($duration, false);
            
            throw $e;
        }
    }

    public function translate(string $text, string $targetLanguage, array $options = []): array
    {
        $prompt = "Traduci il seguente testo in {$targetLanguage}: {$text}";
        
        return $this->generateText($prompt, $options);
    }

    public function analyzeContent(string $content, string $analysisType, array $options = []): array
    {
        $prompt = "Analizza il seguente contenuto per {$analysisType}: {$content}";
        
        return $this->generateText($prompt, $options);
    }

    public function updatePerformance(float $responseTime, bool $success): void
    {
        $this->performanceStats['total_requests']++;
        $this->performanceStats['total_duration'] += $responseTime;
        
        if ($success) {
            $this->performanceStats['successful_requests']++;
        }
        
        $this->performanceStats['average_response_time'] = 
            $this->performanceStats['total_duration'] / $this->performanceStats['total_requests'];
        
        $this->performanceStats['success_rate'] = 
            ($this->performanceStats['successful_requests'] / $this->performanceStats['total_requests']) * 100;
    }

    public function getInfo(): array
    {
        return [
            'name' => $this->getName(),
            'provider' => $this->getProvider(),
            'description' => $this->getDescription(),
            'capabilities' => $this->getCapabilities(),
            'cost_per_token' => $this->getCostPerToken(),
            'max_tokens' => $this->getMaxTokens(),
            'context_window' => $this->getContextWindow(),
            'priority' => $this->getPriority(),
            'available' => $this->isAvailable(),
            'performance' => $this->performanceStats
        ];
    }

    public function supportsCapability(string $capability): bool
    {
        return in_array($capability, $this->getCapabilities());
    }

    public function estimateCost(string $prompt, array $options = []): float
    {
        $estimatedTokens = $this->estimateTokens($prompt);
        return $estimatedTokens * $this->getCostPerToken();
    }

    public function validateOptions(array $options): array
    {
        $validated = [];
        
        if (isset($options['max_tokens'])) {
            $validated['max_tokens'] = min($options['max_tokens'], $this->getMaxTokens());
        }
        
        if (isset($options['temperature'])) {
            $validated['temperature'] = max(0, min(2, $options['temperature']));
        }
        
        if (isset($options['top_p'])) {
            $validated['top_p'] = max(0, min(1, $options['top_p']));
        }
        
        if (isset($options['top_k'])) {
            $validated['top_k'] = max(1, min(100, $options['top_k']));
        }
        
        return $validated;
    }

    public function getLimits(): array
    {
        return [
            'max_tokens' => $this->getMaxTokens(),
            'context_window' => $this->getContextWindow(),
            'max_requests_per_minute' => $this->modelConfig['max_requests_per_minute'] ?? 60,
            'cost_per_token' => $this->getCostPerToken()
        ];
    }

    public function isConfigured(): bool
    {
        $providerConfig = config('ai_models.providers.gemini');
        return !empty($providerConfig['api_key']) && $providerConfig['enabled'];
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('generateContent', [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'test']
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 10
                ]
            ]);
            return isset($response['candidates']);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getStats(): array
    {
        return $this->performanceStats;
    }

    public function resetStats(): void
    {
        $this->performanceStats = [
            'total_requests' => 0,
            'successful_requests' => 0,
            'total_duration' => 0,
            'average_response_time' => 0,
            'success_rate' => 0
        ];
    }

    /**
     * Effettua una richiesta all'API Gemini
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $providerConfig = config('ai_models.providers.gemini');
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])
        ->timeout($providerConfig['timeout'] ?? 30)
        ->post($providerConfig['base_url'] . '/models/' . $this->name . ':' . $endpoint . '?key=' . $providerConfig['api_key'], $data);

        if (!$response->successful()) {
            throw new \Exception('Gemini API Error: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Calcola il costo di una richiesta
     */
    private function calculateCost(int $tokens): float
    {
        return $tokens * $this->getCostPerToken();
    }

    /**
     * Stima il numero di token per un prompt
     */
    private function estimateTokens(string $prompt): int
    {
        // Stima approssimativa: 1 token ≈ 4 caratteri
        return (int) ceil(strlen($prompt) / 4);
    }
}
