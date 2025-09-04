<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\AIProvider;
use App\Models\FallbackLog;
use App\Models\CircuitBreakerState;
use App\Services\AI\CircuitBreakerService;
use App\Services\AI\RetryService;
use App\Services\AI\HealthMonitorService;
use App\Services\AI\FallbackStrategyManager;

class AIFallbackService
{
    private CircuitBreakerService $circuitBreaker;
    private RetryService $retryService;
    private HealthMonitorService $healthMonitor;
    private FallbackStrategyManager $strategyManager;
    private array $config;

    public function __construct(
        CircuitBreakerService $circuitBreaker,
        RetryService $retryService,
        HealthMonitorService $healthMonitor,
        FallbackStrategyManager $strategyManager
    ) {
        $this->circuitBreaker = $circuitBreaker;
        $this->retryService = $retryService;
        $this->healthMonitor = $healthMonitor;
        $this->strategyManager = $strategyManager;
        $this->config = config('ai_fallback', []);
    }

    /**
     * Genera testo con fallback automatico
     */
    public function generateText(string $prompt, array $options = []): array
    {
        $startTime = microtime(true);
        $requestId = uniqid('ai_request_');
        $strategy = $options['strategy'] ?? $this->config['default_strategy'];
        
        try {
            Log::info('AI Fallback: Starting text generation', [
                'request_id' => $requestId,
                'strategy' => $strategy,
                'prompt_length' => strlen($prompt)
            ]);

            $result = $this->strategyManager->executeStrategy($strategy, $prompt, $options);
            
            $responseTime = microtime(true) - $startTime;
            
            // Log successo
            $this->logFallbackEvent($requestId, 'success', [
                'strategy' => $strategy,
                'provider' => $result['provider'] ?? 'unknown',
                'response_time' => $responseTime,
                'tokens_used' => $result['tokens_used'] ?? 0
            ]);

            return array_merge($result, [
                'request_id' => $requestId,
                'response_time' => $responseTime,
                'strategy' => $strategy,
                'success' => true
            ]);

        } catch (\Exception $e) {
            $responseTime = microtime(true) - $startTime;
            
            // Log errore
            $this->logFallbackEvent($requestId, 'error', [
                'strategy' => $strategy,
                'error' => $e->getMessage(),
                'response_time' => $responseTime
            ]);

            // Gestisci errore con fallback
            return $this->handleError($e, $prompt, $options, $requestId, $responseTime);
        }
    }

    /**
     * Genera testo con strategia specifica
     */
    public function generateWithStrategy(string $prompt, string $strategy, array $options = []): array
    {
        $options['strategy'] = $strategy;
        return $this->generateText($prompt, $options);
    }

    /**
     * Gestisce gli errori con fallback
     */
    private function handleError(\Exception $e, string $prompt, array $options, string $requestId, float $responseTime): array
    {
        $errorType = $this->classifyError($e);
        $fallbackStrategy = $this->getFallbackStrategyForError($errorType);
        
        Log::warning('AI Fallback: Handling error with fallback', [
            'request_id' => $requestId,
            'error_type' => $errorType,
            'fallback_strategy' => $fallbackStrategy,
            'error' => $e->getMessage()
        ]);

        try {
            // Prova strategia di fallback
            $result = $this->strategyManager->executeStrategy($fallbackStrategy, $prompt, $options);
            
            $this->logFallbackEvent($requestId, 'fallback_success', [
                'original_error' => $e->getMessage(),
                'error_type' => $errorType,
                'fallback_strategy' => $fallbackStrategy,
                'provider' => $result['provider'] ?? 'unknown'
            ]);

            return array_merge($result, [
                'request_id' => $requestId,
                'response_time' => $responseTime,
                'strategy' => $fallbackStrategy,
                'success' => true,
                'fallback_used' => true,
                'original_error' => $e->getMessage()
            ]);

        } catch (\Exception $fallbackError) {
            Log::error('AI Fallback: Fallback strategy also failed', [
                'request_id' => $requestId,
                'original_error' => $e->getMessage(),
                'fallback_error' => $fallbackError->getMessage(),
                'fallback_strategy' => $fallbackStrategy
            ]);

            // Ultimo resort: risposta statica
            return $this->getStaticFallbackResponse($prompt, $requestId, $responseTime, $e);
        }
    }

    /**
     * Classifica il tipo di errore
     */
    public function classifyError(\Exception $e): string
    {
        $errorMessage = strtolower($e->getMessage());
        $classifiers = $this->config['error_classification']['classifiers'] ?? [];

        foreach ($classifiers as $type => $config) {
            foreach ($config['patterns'] as $pattern) {
                if (strpos($errorMessage, $pattern) !== false) {
                    return $type;
                }
            }
        }

        return 'unknown_error';
    }

    /**
     * Ottiene la strategia di fallback per un tipo di errore
     */
    private function getFallbackStrategyForError(string $errorType): string
    {
        $classifiers = $this->config['error_classification']['classifiers'] ?? [];
        $classifier = $classifiers[$errorType] ?? null;
        
        return $classifier['fallback_strategy'] ?? 'static_fallback';
    }

    /**
     * Ottiene risposta statica di fallback
     */
    private function getStaticFallbackResponse(string $prompt, string $requestId, float $responseTime, \Exception $originalError): array
    {
        $staticResponses = $this->config['strategies']['static_fallback']['static_responses'] ?? [];
        $defaultResponse = $this->config['strategies']['static_fallback']['default_response'] ?? 
            'I apologize, but I am currently unable to process your request. Please try again later.';

        $response = $staticResponses[$prompt] ?? $defaultResponse;

        $this->logFallbackEvent($requestId, 'static_fallback', [
            'prompt' => $prompt,
            'original_error' => $originalError->getMessage(),
            'response' => $response
        ]);

        return [
            'request_id' => $requestId,
            'response' => $response,
            'response_time' => $responseTime,
            'strategy' => 'static_fallback',
            'provider' => 'static',
            'success' => true,
            'fallback_used' => true,
            'static_response' => true,
            'original_error' => $originalError->getMessage()
        ];
    }

    /**
     * Retry di una richiesta
     */
    public function retryRequest(string $requestId): array
    {
        $log = FallbackLog::where('request_id', $requestId)->first();
        
        if (!$log) {
            throw new \Exception("Request not found: {$requestId}");
        }

        $options = json_decode($log->context, true);
        $prompt = $options['prompt'] ?? '';
        $strategy = $options['strategy'] ?? $this->config['default_strategy'];

        Log::info('AI Fallback: Retrying request', [
            'request_id' => $requestId,
            'strategy' => $strategy
        ]);

        return $this->generateText($prompt, $options);
    }

    /**
     * Ottiene lo stato del circuit breaker
     */
    public function getCircuitBreakerState(string $provider): array
    {
        return $this->circuitBreaker->getState($provider);
    }

    /**
     * Reset del circuit breaker
     */
    public function resetCircuitBreaker(string $provider): bool
    {
        return $this->circuitBreaker->reset($provider);
    }

    /**
     * Ottiene la salute di un provider
     */
    public function getProviderHealth(string $provider): array
    {
        return $this->healthMonitor->getProviderHealth($provider);
    }

    /**
     * Ottiene la salute di tutti i provider
     */
    public function getAllProvidersHealth(): array
    {
        return $this->healthMonitor->getAllProvidersHealth();
    }

    /**
     * Ottiene i log di fallback
     */
    public function getFallbackLogs(array $filters = []): array
    {
        $query = FallbackLog::query();

        if (isset($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }

        if (isset($filters['strategy'])) {
            $query->where('strategy', $filters['strategy']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($filters['limit'] ?? 100)
            ->get()
            ->toArray();
    }

    /**
     * Ottiene statistiche di fallback
     */
    public function getFallbackStatistics(): array
    {
        $totalRequests = FallbackLog::count();
        $successfulRequests = FallbackLog::where('status', 'success')->count();
        $failedRequests = FallbackLog::where('status', 'error')->count();
        $fallbackRequests = FallbackLog::where('status', 'fallback_success')->count();

        $successRate = $totalRequests > 0 ? ($successfulRequests / $totalRequests) * 100 : 0;
        $fallbackRate = $totalRequests > 0 ? ($fallbackRequests / $totalRequests) * 100 : 0;

        $providerStats = FallbackLog::select('provider')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(CASE WHEN status = "success" THEN 1 END) as successful')
            ->selectRaw('COUNT(CASE WHEN status = "error" THEN 1 END) as failed')
            ->selectRaw('COUNT(CASE WHEN status = "fallback_success" THEN 1 END) as fallback')
            ->groupBy('provider')
            ->get()
            ->map(function($stat) {
                $total = $stat->total;
                return [
                    'provider' => $stat->provider,
                    'total' => $total,
                    'successful' => $stat->successful,
                    'failed' => $stat->failed,
                    'fallback' => $stat->fallback,
                    'success_rate' => $total > 0 ? round(($stat->successful / $total) * 100, 2) : 0,
                    'fallback_rate' => $total > 0 ? round(($stat->fallback / $total) * 100, 2) : 0
                ];
            });

        $strategyStats = FallbackLog::select('strategy')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('AVG(response_time) as avg_response_time')
            ->groupBy('strategy')
            ->get()
            ->map(function($stat) {
                return [
                    'strategy' => $stat->strategy,
                    'total' => $stat->total,
                    'avg_response_time' => round($stat->avg_response_time, 3)
                ];
            });

        return [
            'overview' => [
                'total_requests' => $totalRequests,
                'successful_requests' => $successfulRequests,
                'failed_requests' => $failedRequests,
                'fallback_requests' => $fallbackRequests,
                'success_rate' => round($successRate, 2),
                'fallback_rate' => round($fallbackRate, 2)
            ],
            'provider_stats' => $providerStats,
            'strategy_stats' => $strategyStats
        ];
    }

    /**
     * Ottiene i provider disponibili
     */
    public function getAvailableProviders(): array
    {
        $providers = $this->config['providers'] ?? [];
        $availableProviders = [];

        foreach ($providers as $name => $config) {
            if ($config['enabled']) {
                $health = $this->healthMonitor->getProviderHealth($name);
                $circuitState = $this->circuitBreaker->getState($name);

                $availableProviders[] = [
                    'name' => $name,
                    'description' => $config['description'] ?? '',
                    'priority' => $config['priority'] ?? 999,
                    'health' => $health,
                    'circuit_breaker_state' => $circuitState['state'] ?? 'closed',
                    'enabled' => $config['enabled']
                ];
            }
        }

        // Ordina per priorità
        usort($availableProviders, function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $availableProviders;
    }

    /**
     * Abilita/disabilita un provider
     */
    public function toggleProvider(string $provider, bool $enabled): bool
    {
        $providers = $this->config['providers'] ?? [];
        
        if (!isset($providers[$provider])) {
            return false;
        }

        $providers[$provider]['enabled'] = $enabled;
        
        // In un'implementazione reale, questo dovrebbe aggiornare la configurazione
        Log::info('AI Fallback: Provider toggled', [
            'provider' => $provider,
            'enabled' => $enabled
        ]);

        return true;
    }

    /**
     * Ottiene le strategie disponibili
     */
    public function getAvailableStrategies(): array
    {
        $strategies = $this->config['strategies'] ?? [];
        $availableStrategies = [];

        foreach ($strategies as $name => $config) {
            $availableStrategies[] = [
                'name' => $name,
                'description' => $config['description'] ?? '',
                'class' => $config['class'] ?? '',
                'enabled' => true
            ];
        }

        return $availableStrategies;
    }

    /**
     * Log di un evento di fallback
     */
    private function logFallbackEvent(string $requestId, string $status, array $context): void
    {
        if (!$this->config['logging']['enabled']) {
            return;
        }

        try {
            FallbackLog::create([
                'request_id' => $requestId,
                'status' => $status,
                'provider' => $context['provider'] ?? 'unknown',
                'strategy' => $context['strategy'] ?? 'unknown',
                'response_time' => $context['response_time'] ?? 0,
                'context' => json_encode($context),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('AI Fallback: Failed to log event', [
                'request_id' => $requestId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Pulisce i log vecchi
     */
    public function cleanupOldLogs(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        return FallbackLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Ottiene le metriche in tempo reale
     */
    public function getRealTimeMetrics(): array
    {
        $lastHour = now()->subHour();
        
        $recentLogs = FallbackLog::where('created_at', '>=', $lastHour)->get();
        
        $totalRequests = $recentLogs->count();
        $successfulRequests = $recentLogs->where('status', 'success')->count();
        $failedRequests = $recentLogs->where('status', 'error')->count();
        $fallbackRequests = $recentLogs->where('status', 'fallback_success')->count();

        return [
            'last_hour' => [
                'total_requests' => $totalRequests,
                'successful_requests' => $successfulRequests,
                'failed_requests' => $failedRequests,
                'fallback_requests' => $fallbackRequests,
                'success_rate' => $totalRequests > 0 ? round(($successfulRequests / $totalRequests) * 100, 2) : 0,
                'fallback_rate' => $totalRequests > 0 ? round(($fallbackRequests / $totalRequests) * 100, 2) : 0
            ],
            'circuit_breaker_states' => $this->getCircuitBreakerStates(),
            'provider_health' => $this->getAllProvidersHealth()
        ];
    }

    /**
     * Ottiene gli stati dei circuit breaker
     */
    private function getCircuitBreakerStates(): array
    {
        $providers = array_keys($this->config['providers'] ?? []);
        $states = [];

        foreach ($providers as $provider) {
            $states[$provider] = $this->circuitBreaker->getState($provider);
        }

        return $states;
    }
}
