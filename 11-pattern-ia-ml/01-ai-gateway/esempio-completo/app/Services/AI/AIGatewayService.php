<?php

namespace App\Services\AI;

use App\Models\AIRequest;
use App\Services\AI\Providers\AIProviderInterface;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\GeminiProvider;

class AIGatewayService
{
    private array $providers = [];
    private array $fallbackOrder = ['openai', 'claude', 'gemini'];

    public function __construct()
    {
        $this->providers = [
            'openai' => new OpenAIProvider(),
            'claude' => new ClaudeProvider(),
            'gemini' => new GeminiProvider(),
        ];
    }

    public function chat(string $prompt, ?string $preferredProvider = null, array $options = []): array
    {
        $providersToTry = $preferredProvider 
            ? [$preferredProvider, ...array_diff($this->fallbackOrder, [$preferredProvider])]
            : $this->fallbackOrder;

        $lastError = null;

        foreach ($providersToTry as $providerName) {
            if (!isset($this->providers[$providerName])) {
                continue;
            }

            $provider = $this->providers[$providerName];
            
            if (!$provider->isAvailable()) {
                continue;
            }

            try {
                $result = $provider->chat($prompt, $options);
                
                if ($result['success']) {
                    // Salva la richiesta nel database
                    $this->logRequest($prompt, $result, $providerName);
                    return $result;
                }
                
                $lastError = $result['error'] ?? 'Unknown error';
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        return [
            'success' => false,
            'error' => 'All providers failed. Last error: ' . ($lastError ?? 'Unknown error'),
            'provider' => 'none'
        ];
    }

    public function getAvailableProviders(): array
    {
        $available = [];
        
        foreach ($this->providers as $name => $provider) {
            if ($provider->isAvailable()) {
                $available[$name] = [
                    'name' => $provider->getName(),
                    'cost_per_token' => $provider->getCostPerToken()
                ];
            }
        }
        
        return $available;
    }

    public function getProviderStats(): array
    {
        $stats = [];
        
        foreach ($this->providers as $name => $provider) {
            $stats[$name] = [
                'name' => $provider->getName(),
                'available' => $provider->isAvailable(),
                'cost_per_token' => $provider->getCostPerToken()
            ];
        }
        
        return $stats;
    }

    private function logRequest(string $prompt, array $result, string $provider): void
    {
        try {
            AIRequest::create([
                'provider' => $provider,
                'prompt' => $prompt,
                'response' => $result['response'] ?? '',
                'tokens_used' => $result['tokens_used'] ?? 0,
                'cost' => $result['cost'] ?? 0,
                'response_time' => $result['response_time'] ?? 0,
                'status' => $result['success'] ? 'success' : 'error',
                'error_message' => $result['error'] ?? null
            ]);
        } catch (\Exception $e) {
            // Log dell'errore ma non interrompere il flusso
            \Log::error('Failed to log AI request: ' . $e->getMessage());
        }
    }
}
