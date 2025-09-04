<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\AIRequest;

class AIGatewayService
{
    private array $providers = [];
    private array $config;
    private RateLimiter $rateLimiter;
    private CacheService $cacheService;

    public function __construct(RateLimiter $rateLimiter, CacheService $cacheService)
    {
        $this->config = config('ai');
        $this->rateLimiter = $rateLimiter;
        $this->cacheService = $cacheService;
        $this->initializeProviders();
    }

    private function initializeProviders(): void
    {
        foreach ($this->config['providers'] as $name => $config) {
            if (!$config['enabled']) {
                continue;
            }

            $this->providers[$name] = new $this->getProviderClass($name)($config);
        }

        // Ordina per priorità
        uasort($this->providers, fn($a, $b) => $a->getPriority() - $b->getPriority());
    }

    private function getProviderClass(string $name): string
    {
        $providers = [
            'openai' => OpenAIProvider::class,
            'claude' => ClaudeProvider::class,
            'gemini' => GeminiProvider::class,
        ];

        return $providers[$name] ?? throw new \InvalidArgumentException("Provider {$name} non supportato");
    }

    public function generateText(string $prompt, array $options = []): array
    {
        $requestId = uniqid();
        $startTime = microtime(true);

        Log::info('AI Gateway Request Started', [
            'request_id' => $requestId,
            'prompt_length' => strlen($prompt),
            'options' => $options
        ]);

        try {
            // Controlla cache
            $cacheKey = $this->cacheService->generateKey($prompt, $options);
            $cached = $this->cacheService->get($cacheKey);
            
            if ($cached) {
                Log::info('AI Gateway Cache Hit', ['request_id' => $requestId]);
                return array_merge($cached, ['cached' => true]);
            }

            // Seleziona provider
            $provider = $this->selectProvider($options);
            
            if (!$provider) {
                throw new \Exception('Nessun provider AI disponibile');
            }

            // Controlla rate limiting
            if (!$this->rateLimiter->checkLimit($provider->getName())) {
                throw new \Exception('Rate limit superato per provider ' . $provider->getName());
            }

            // Genera testo
            $result = $provider->generateText($prompt, $options);
            $duration = microtime(true) - $startTime;

            // Calcola costo
            $cost = $this->calculateCost($provider, $result);

            $response = [
                'text' => $result['text'],
                'provider' => $provider->getName(),
                'model' => $provider->getModel(),
                'duration' => $duration,
                'cost' => $cost,
                'tokens_used' => $result['tokens_used'] ?? 0,
                'cached' => false,
                'request_id' => $requestId
            ];

            // Cache la risposta
            $this->cacheService->set($cacheKey, $response);

            // Salva nel database
            $this->saveRequest($requestId, $provider->getName(), $prompt, $response, true);

            Log::info('AI Gateway Request Completed', [
                'request_id' => $requestId,
                'provider' => $provider->getName(),
                'duration' => $duration,
                'cost' => $cost
            ]);

            return $response;

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            
            Log::error('AI Gateway Request Failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'duration' => $duration
            ]);

            // Salva errore nel database
            $this->saveRequest($requestId, 'unknown', $prompt, ['error' => $e->getMessage()], false);

            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): array
    {
        $requestId = uniqid();
        $startTime = microtime(true);

        Log::info('AI Gateway Image Request Started', [
            'request_id' => $requestId,
            'prompt_length' => strlen($prompt)
        ]);

        try {
            // Seleziona provider che supporta immagini
            $provider = $this->selectProvider($options, ['image_generation']);
            
            if (!$provider) {
                throw new \Exception('Nessun provider AI supporta la generazione di immagini');
            }

            // Controlla rate limiting
            if (!$this->rateLimiter->checkLimit($provider->getName())) {
                throw new \Exception('Rate limit superato per provider ' . $provider->getName());
            }

            // Genera immagine
            $result = $provider->generateImage($prompt, $options);
            $duration = microtime(true) - $startTime;

            $response = [
                'image_url' => $result['image_url'],
                'provider' => $provider->getName(),
                'model' => $provider->getModel(),
                'duration' => $duration,
                'cost' => $result['cost'] ?? 0,
                'request_id' => $requestId
            ];

            // Salva nel database
            $this->saveRequest($requestId, $provider->getName(), $prompt, $response, true);

            Log::info('AI Gateway Image Request Completed', [
                'request_id' => $requestId,
                'provider' => $provider->getName(),
                'duration' => $duration
            ]);

            return $response;

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            
            Log::error('AI Gateway Image Request Failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'duration' => $duration
            ]);

            throw $e;
        }
    }

    public function translate(string $text, string $targetLanguage, array $options = []): array
    {
        $prompt = "Traduci il seguente testo in {$targetLanguage}: {$text}";
        $options['task'] = 'translation';
        
        return $this->generateText($prompt, $options);
    }

    private function selectProvider(array $options = [], array $requiredCapabilities = []): ?AIProviderInterface
    {
        $strategy = $this->config['fallback_strategy'];
        
        switch ($strategy) {
            case 'priority':
                return $this->selectByPriority($requiredCapabilities);
            case 'cost':
                return $this->selectByCost($requiredCapabilities);
            case 'performance':
                return $this->selectByPerformance($requiredCapabilities);
            default:
                return $this->selectByPriority($requiredCapabilities);
        }
    }

    private function selectByPriority(array $requiredCapabilities = []): ?AIProviderInterface
    {
        foreach ($this->providers as $provider) {
            if (!$provider->isAvailable()) {
                continue;
            }

            if ($this->hasRequiredCapabilities($provider, $requiredCapabilities)) {
                return $provider;
            }
        }

        return null;
    }

    private function selectByCost(array $requiredCapabilities = []): ?AIProviderInterface
    {
        $availableProviders = array_filter($this->providers, function($provider) use ($requiredCapabilities) {
            return $provider->isAvailable() && $this->hasRequiredCapabilities($provider, $requiredCapabilities);
        });

        if (empty($availableProviders)) {
            return null;
        }

        usort($availableProviders, fn($a, $b) => $a->getCostPerToken() <=> $b->getCostPerToken());
        
        return reset($availableProviders);
    }

    private function selectByPerformance(array $requiredCapabilities = []): ?AIProviderInterface
    {
        $availableProviders = array_filter($this->providers, function($provider) use ($requiredCapabilities) {
            return $provider->isAvailable() && $this->hasRequiredCapabilities($provider, $requiredCapabilities);
        });

        if (empty($availableProviders)) {
            return null;
        }

        // Seleziona il provider con la migliore performance storica
        usort($availableProviders, fn($a, $b) => $b->getAverageResponseTime() <=> $a->getAverageResponseTime());
        
        return reset($availableProviders);
    }

    private function hasRequiredCapabilities(AIProviderInterface $provider, array $requiredCapabilities): bool
    {
        if (empty($requiredCapabilities)) {
            return true;
        }

        $providerCapabilities = $provider->getCapabilities();
        
        return !array_diff($requiredCapabilities, $providerCapabilities);
    }

    private function calculateCost(AIProviderInterface $provider, array $result): float
    {
        $tokensUsed = $result['tokens_used'] ?? 0;
        $costPerToken = $provider->getCostPerToken();
        
        return $tokensUsed * $costPerToken;
    }

    private function saveRequest(string $requestId, string $provider, string $prompt, array $response, bool $success): void
    {
        if (!$this->config['monitoring']['save_to_database']) {
            return;
        }

        try {
            AIRequest::create([
                'request_id' => $requestId,
                'provider' => $provider,
                'prompt' => $prompt,
                'response' => $response,
                'success' => $success,
                'duration' => $response['duration'] ?? 0,
                'cost' => $response['cost'] ?? 0,
                'tokens_used' => $response['tokens_used'] ?? 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save AI request to database', [
                'request_id' => $requestId,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getProviderStatus(): array
    {
        $status = [];
        
        foreach ($this->providers as $name => $provider) {
            $status[] = [
                'name' => $name,
                'available' => $provider->isAvailable(),
                'priority' => $provider->getPriority(),
                'cost_per_token' => $provider->getCostPerToken(),
                'capabilities' => $provider->getCapabilities(),
                'last_used' => $this->getLastUsedTime($name)
            ];
        }
        
        return $status;
    }

    public function getMetrics(): array
    {
        $metrics = Cache::get('ai_gateway_metrics', []);
        
        return [
            'total_requests' => $metrics['total_requests'] ?? 0,
            'successful_requests' => $metrics['successful_requests'] ?? 0,
            'failed_requests' => $metrics['failed_requests'] ?? 0,
            'total_cost' => $metrics['total_cost'] ?? 0,
            'average_response_time' => $metrics['average_response_time'] ?? 0,
            'cache_hit_rate' => $metrics['cache_hit_rate'] ?? 0,
            'provider_usage' => $metrics['provider_usage'] ?? [],
        ];
    }

    private function getLastUsedTime(string $providerName): ?string
    {
        $lastRequest = AIRequest::where('provider', $providerName)
            ->where('success', true)
            ->orderBy('created_at', 'desc')
            ->first();
            
        return $lastRequest ? $lastRequest->created_at->toISOString() : null;
    }

    public function clearCache(): bool
    {
        return $this->cacheService->clear();
    }

    public function getCacheStats(): array
    {
        return $this->cacheService->getStats();
    }
}
