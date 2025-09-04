<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\CircuitBreakerState;

class CircuitBreakerService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('ai_fallback', []);
    }

    /**
     * Verifica se il circuit breaker è aperto
     */
    public function isOpen(string $provider): bool
    {
        $state = $this->getState($provider);
        return $state['state'] === 'open';
    }

    /**
     * Verifica se il circuit breaker è chiuso
     */
    public function isClosed(string $provider): bool
    {
        $state = $this->getState($provider);
        return $state['state'] === 'closed';
    }

    /**
     * Verifica se il circuit breaker è half-open
     */
    public function isHalfOpen(string $provider): bool
    {
        $state = $this->getState($provider);
        return $state['state'] === 'half_open';
    }

    /**
     * Ottiene lo stato del circuit breaker
     */
    public function getState(string $provider): array
    {
        $cacheKey = "circuit_breaker_state_{$provider}";
        $state = Cache::get($cacheKey);

        if (!$state) {
            $state = $this->createInitialState($provider);
        }

        // Verifica se è il momento di passare a half-open
        if ($state['state'] === 'open' && $this->shouldTransitionToHalfOpen($state)) {
            $state = $this->transitionToHalfOpen($provider, $state);
        }

        return $state;
    }

    /**
     * Registra un successo
     */
    public function recordSuccess(string $provider): void
    {
        $state = $this->getState($provider);
        
        if ($state['state'] === 'half_open') {
            $state['success_count']++;
            
            // Se abbiamo abbastanza successi, chiudi il circuit breaker
            if ($state['success_count'] >= $this->config['circuit_breaker']['success_threshold']) {
                $state = $this->transitionToClosed($provider, $state);
            }
        } elseif ($state['state'] === 'closed') {
            // Reset del contatore di errori in caso di successo
            $state['failure_count'] = 0;
        }

        $this->saveState($provider, $state);
        
        Log::debug('Circuit Breaker: Success recorded', [
            'provider' => $provider,
            'state' => $state['state']
        ]);
    }

    /**
     * Registra un fallimento
     */
    public function recordFailure(string $provider): void
    {
        $state = $this->getState($provider);
        $state['failure_count']++;
        $state['last_failure_time'] = now()->timestamp;

        // Se siamo in half-open, un fallimento ci riporta a open
        if ($state['state'] === 'half_open') {
            $state = $this->transitionToOpen($provider, $state);
        } elseif ($state['state'] === 'closed' && $this->shouldTransitionToOpen($state)) {
            $state = $this->transitionToOpen($provider, $state);
        }

        $this->saveState($provider, $state);
        
        Log::warning('Circuit Breaker: Failure recorded', [
            'provider' => $provider,
            'state' => $state['state'],
            'failure_count' => $state['failure_count']
        ]);
    }

    /**
     * Reset del circuit breaker
     */
    public function reset(string $provider): bool
    {
        try {
            $state = $this->createInitialState($provider);
            $this->saveState($provider, $state);
            
            Log::info('Circuit Breaker: Reset', [
                'provider' => $provider
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Circuit Breaker: Reset failed', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Ottiene le statistiche del circuit breaker
     */
    public function getStatistics(): array
    {
        $providers = array_keys($this->config['providers'] ?? []);
        $stats = [];

        foreach ($providers as $provider) {
            $state = $this->getState($provider);
            $stats[$provider] = [
                'state' => $state['state'],
                'failure_count' => $state['failure_count'],
                'success_count' => $state['success_count'] ?? 0,
                'last_failure_time' => $state['last_failure_time'] ?? null,
                'opened_at' => $state['opened_at'] ?? null,
                'half_opened_at' => $state['half_opened_at'] ?? null
            ];
        }

        return $stats;
    }

    /**
     * Crea lo stato iniziale del circuit breaker
     */
    private function createInitialState(string $provider): array
    {
        return [
            'state' => 'closed',
            'failure_count' => 0,
            'success_count' => 0,
            'last_failure_time' => null,
            'opened_at' => null,
            'half_opened_at' => null,
            'created_at' => now()->timestamp
        ];
    }

    /**
     * Verifica se dovrebbe passare a half-open
     */
    private function shouldTransitionToHalfOpen(array $state): bool
    {
        if ($state['state'] !== 'open') {
            return false;
        }

        $recoveryTimeout = $this->config['circuit_breaker']['recovery_timeout'];
        $openedAt = $state['opened_at'] ?? 0;
        
        return (now()->timestamp - $openedAt) >= $recoveryTimeout;
    }

    /**
     * Verifica se dovrebbe passare a open
     */
    private function shouldTransitionToOpen(array $state): bool
    {
        $failureThreshold = $this->config['circuit_breaker']['failure_threshold'];
        return $state['failure_count'] >= $failureThreshold;
    }

    /**
     * Passa a stato open
     */
    private function transitionToOpen(string $provider, array $state): array
    {
        $state['state'] = 'open';
        $state['opened_at'] = now()->timestamp;
        $state['half_opened_at'] = null;
        $state['success_count'] = 0;

        Log::warning('Circuit Breaker: Transitioned to OPEN', [
            'provider' => $provider,
            'failure_count' => $state['failure_count']
        ]);

        return $state;
    }

    /**
     * Passa a stato half-open
     */
    private function transitionToHalfOpen(string $provider, array $state): array
    {
        $state['state'] = 'half_open';
        $state['half_opened_at'] = now()->timestamp;
        $state['success_count'] = 0;

        Log::info('Circuit Breaker: Transitioned to HALF-OPEN', [
            'provider' => $provider
        ]);

        return $state;
    }

    /**
     * Passa a stato closed
     */
    private function transitionToClosed(string $provider, array $state): array
    {
        $state['state'] = 'closed';
        $state['failure_count'] = 0;
        $state['success_count'] = 0;
        $state['opened_at'] = null;
        $state['half_opened_at'] = null;

        Log::info('Circuit Breaker: Transitioned to CLOSED', [
            'provider' => $provider
        ]);

        return $state;
    }

    /**
     * Salva lo stato del circuit breaker
     */
    private function saveState(string $provider, array $state): void
    {
        $cacheKey = "circuit_breaker_state_{$provider}";
        $ttl = $this->config['circuit_breaker']['recovery_timeout'] * 2; // TTL più lungo del recovery timeout
        
        Cache::put($cacheKey, $state, $ttl);
        
        // Salva anche nel database per persistenza
        $this->saveStateToDatabase($provider, $state);
    }

    /**
     * Salva lo stato nel database
     */
    private function saveStateToDatabase(string $provider, array $state): void
    {
        try {
            CircuitBreakerState::updateOrCreate(
                ['provider' => $provider],
                [
                    'state' => $state['state'],
                    'failure_count' => $state['failure_count'],
                    'success_count' => $state['success_count'],
                    'last_failure_time' => $state['last_failure_time'] ? 
                        \Carbon\Carbon::createFromTimestamp($state['last_failure_time']) : null,
                    'opened_at' => $state['opened_at'] ? 
                        \Carbon\Carbon::createFromTimestamp($state['opened_at']) : null,
                    'half_opened_at' => $state['half_opened_at'] ? 
                        \Carbon\Carbon::createFromTimestamp($state['half_opened_at']) : null,
                    'context' => json_encode($state)
                ]
            );
        } catch (\Exception $e) {
            Log::error('Circuit Breaker: Failed to save state to database', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Carica lo stato dal database
     */
    private function loadStateFromDatabase(string $provider): ?array
    {
        try {
            $record = CircuitBreakerState::where('provider', $provider)->first();
            
            if (!$record) {
                return null;
            }

            return [
                'state' => $record->state,
                'failure_count' => $record->failure_count,
                'success_count' => $record->success_count,
                'last_failure_time' => $record->last_failure_time?->timestamp,
                'opened_at' => $record->opened_at?->timestamp,
                'half_opened_at' => $record->half_opened_at?->timestamp,
                'created_at' => $record->created_at->timestamp
            ];
        } catch (\Exception $e) {
            Log::error('Circuit Breaker: Failed to load state from database', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Ottiene i provider con circuit breaker aperto
     */
    public function getOpenProviders(): array
    {
        $providers = array_keys($this->config['providers'] ?? []);
        $openProviders = [];

        foreach ($providers as $provider) {
            if ($this->isOpen($provider)) {
                $state = $this->getState($provider);
                $openProviders[] = [
                    'provider' => $provider,
                    'state' => $state,
                    'opened_duration' => now()->timestamp - ($state['opened_at'] ?? 0)
                ];
            }
        }

        return $openProviders;
    }

    /**
     * Ottiene i provider con circuit breaker half-open
     */
    public function getHalfOpenProviders(): array
    {
        $providers = array_keys($this->config['providers'] ?? []);
        $halfOpenProviders = [];

        foreach ($providers as $provider) {
            if ($this->isHalfOpen($provider)) {
                $state = $this->getState($provider);
                $halfOpenProviders[] = [
                    'provider' => $provider,
                    'state' => $state,
                    'half_opened_duration' => now()->timestamp - ($state['half_opened_at'] ?? 0)
                ];
            }
        }

        return $halfOpenProviders;
    }

    /**
     * Ottiene le metriche aggregate
     */
    public function getAggregateMetrics(): array
    {
        $providers = array_keys($this->config['providers'] ?? []);
        $metrics = [
            'total_providers' => count($providers),
            'closed_providers' => 0,
            'open_providers' => 0,
            'half_open_providers' => 0,
            'total_failures' => 0,
            'total_successes' => 0
        ];

        foreach ($providers as $provider) {
            $state = $this->getState($provider);
            
            switch ($state['state']) {
                case 'closed':
                    $metrics['closed_providers']++;
                    break;
                case 'open':
                    $metrics['open_providers']++;
                    break;
                case 'half_open':
                    $metrics['half_open_providers']++;
                    break;
            }
            
            $metrics['total_failures'] += $state['failure_count'];
            $metrics['total_successes'] += $state['success_count'];
        }

        return $metrics;
    }

    /**
     * Pulisce gli stati vecchi
     */
    public function cleanupOldStates(int $days = 7): int
    {
        $cutoffDate = now()->subDays($days);
        return CircuitBreakerState::where('created_at', '<', $cutoffDate)->delete();
    }
}
