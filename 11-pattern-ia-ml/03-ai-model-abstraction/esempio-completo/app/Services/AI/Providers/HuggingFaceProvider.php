<?php

namespace App\Services\AI\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuggingFaceProvider implements AIModelInterface
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
        return 'huggingface';
    }

    public function getDescription(): string
    {
        return $this->modelConfig['description'] ?? 'Hugging Face Model';
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
        return $this->modelConfig['max_tokens'] ?? 1024;
    }

    public function getContextWindow(): int
    {
        return $this->modelConfig['context_window'] ?? 1024;
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
            $response = $this->makeRequest('', [
                'inputs' => $prompt,
                'parameters' => [
                    'max_new_tokens' => $options['max_tokens'] ?? $this->getMaxTokens(),
                    'temperature' => $options['temperature'] ?? 0.7,
                    'top_p' => $options['top_p'] ?? 1.0,
                    'do_sample' => true,
                    'return_full_text' => false
                ]
            ]);

            $duration = microtime(true) - $startTime;
            $this->updatePerformance($duration, true);

            $generatedText = '';
            if (is_array($response) && isset($response[0]['generated_text'])) {
                $generatedText = $response[0]['generated_text'];
            } elseif (is_string($response)) {
                $generatedText = $response;
            }

            return [
                'text' => $generatedText,
                'tokens_used' => $this->estimateTokens($generatedText),
                'model' => $this->name,
                'provider' => $this->getProvider(),
                'duration' => $duration,
                'cost' => $this->calculateCost($this->estimateTokens($generatedText))
            ];

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            $this->updatePerformance($duration, false);
            
            Log::error('Hugging Face API Error', [
                'model' => $this->name,
                'error' => $e->getMessage(),
                'duration' => $duration
            ]);
            
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): array
    {
        throw new \Exception('Hugging Face non supporta la generazione di immagini in questo esempio');
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
        
        return $validated;
    }

    public function getLimits(): array
    {
        return [
            'max_tokens' => $this->getMaxTokens(),
            'context_window' => $this->getContextWindow(),
            'max_requests_per_minute' => $this->modelConfig['max_requests_per_minute'] ?? 100,
            'cost_per_token' => $this->getCostPerToken()
        ];
    }

    public function isConfigured(): bool
    {
        $providerConfig = config('ai_models.providers.huggingface');
        return !empty($providerConfig['api_key']) && $providerConfig['enabled'];
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('', [
                'inputs' => 'test',
                'parameters' => [
                    'max_new_tokens' => 10
                ]
            ]);
            return !empty($response);
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
     * Effettua una richiesta all'API Hugging Face
     */
    private function makeRequest(string $endpoint, array $data): array
    {
        $providerConfig = config('ai_models.providers.huggingface');
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $providerConfig['api_key'],
            'Content-Type' => 'application/json'
        ])
        ->timeout($providerConfig['timeout'] ?? 30)
        ->post($providerConfig['base_url'] . '/' . $this->name . $endpoint, $data);

        if (!$response->successful()) {
            throw new \Exception('Hugging Face API Error: ' . $response->body());
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
