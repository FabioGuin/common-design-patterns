<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RetryService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('ai_fallback', []);
    }

    /**
     * Esegue un'operazione con retry
     */
    public function executeWithRetry(callable $operation, array $options = []): mixed
    {
        if (!$this->config['retry']['enabled']) {
            return $operation();
        }

        $maxAttempts = $options['max_attempts'] ?? $this->config['retry']['max_attempts'];
        $backoffStrategy = $options['backoff_strategy'] ?? $this->config['retry']['backoff_strategy'];
        $baseDelay = $options['base_delay'] ?? $this->config['retry']['base_delay'];
        $maxDelay = $options['max_delay'] ?? $this->config['retry']['max_delay'];
        $jitter = $options['jitter'] ?? $this->config['retry']['jitter'];

        $attempt = 1;
        $lastException = null;

        while ($attempt <= $maxAttempts) {
            try {
                $result = $operation();
                
                if ($attempt > 1) {
                    Log::info('Retry Service: Operation succeeded after retry', [
                        'attempt' => $attempt,
                        'max_attempts' => $maxAttempts
                    ]);
                }

                return $result;

            } catch (\Exception $e) {
                $lastException = $e;
                
                // Verifica se l'errore è retryable
                if (!$this->isRetryableError($e)) {
                    Log::warning('Retry Service: Non-retryable error encountered', [
                        'error' => $e->getMessage(),
                        'attempt' => $attempt
                    ]);
                    throw $e;
                }

                // Se è l'ultimo tentativo, rilancia l'eccezione
                if ($attempt >= $maxAttempts) {
                    Log::error('Retry Service: Max attempts reached', [
                        'attempt' => $attempt,
                        'max_attempts' => $maxAttempts,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }

                // Calcola il delay per il prossimo tentativo
                $delay = $this->calculateDelay($attempt, $backoffStrategy, $baseDelay, $maxDelay, $jitter);
                
                Log::warning('Retry Service: Retrying operation', [
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'delay_ms' => $delay,
                    'error' => $e->getMessage()
                ]);

                // Attendi prima del prossimo tentativo
                usleep($delay * 1000);
                
                $attempt++;
            }
        }

        // Questo non dovrebbe mai essere raggiunto, ma per sicurezza
        throw $lastException ?? new \Exception('Retry service failed unexpectedly');
    }

    /**
     * Verifica se un errore è retryable
     */
    private function isRetryableError(\Exception $e): bool
    {
        $retryableErrors = $this->config['retry']['retryable_errors'] ?? [];
        $errorMessage = strtolower($e->getMessage());

        foreach ($retryableErrors as $pattern) {
            if (strpos($errorMessage, $pattern) !== false) {
                return true;
            }
        }

        // Verifica anche il tipo di eccezione
        $retryableExceptionTypes = [
            \GuzzleHttp\Exception\ConnectException::class,
            \GuzzleHttp\Exception\ServerException::class,
            \GuzzleHttp\Exception\TooManyRedirectsException::class,
            \Illuminate\Http\Client\ConnectionException::class,
            \Illuminate\Http\Client\ServerException::class
        ];

        foreach ($retryableExceptionTypes as $exceptionType) {
            if ($e instanceof $exceptionType) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calcola il delay per il prossimo tentativo
     */
    private function calculateDelay(int $attempt, string $strategy, int $baseDelay, int $maxDelay, bool $jitter): int
    {
        $delay = $baseDelay;

        switch ($strategy) {
            case 'exponential':
                $delay = $baseDelay * pow(2, $attempt - 1);
                break;
            case 'linear':
                $delay = $baseDelay * $attempt;
                break;
            case 'fixed':
                $delay = $baseDelay;
                break;
            default:
                $delay = $baseDelay;
        }

        // Applica il jitter per evitare thundering herd
        if ($jitter) {
            $jitterAmount = $delay * 0.1; // 10% di jitter
            $delay += rand(-$jitterAmount, $jitterAmount);
        }

        // Assicurati che il delay non superi il massimo
        return min($delay, $maxDelay);
    }

    /**
     * Esegue un'operazione con retry esponenziale
     */
    public function executeWithExponentialBackoff(callable $operation, array $options = []): mixed
    {
        $options['backoff_strategy'] = 'exponential';
        return $this->executeWithRetry($operation, $options);
    }

    /**
     * Esegue un'operazione con retry lineare
     */
    public function executeWithLinearBackoff(callable $operation, array $options = []): mixed
    {
        $options['backoff_strategy'] = 'linear';
        return $this->executeWithRetry($operation, $options);
    }

    /**
     * Esegue un'operazione con retry fisso
     */
    public function executeWithFixedBackoff(callable $operation, array $options = []): mixed
    {
        $options['backoff_strategy'] = 'fixed';
        return $this->executeWithRetry($operation, $options);
    }

    /**
     * Esegue un'operazione con retry personalizzato
     */
    public function executeWithCustomBackoff(callable $operation, callable $delayCalculator, array $options = []): mixed
    {
        $maxAttempts = $options['max_attempts'] ?? $this->config['retry']['max_attempts'];
        $attempt = 1;
        $lastException = null;

        while ($attempt <= $maxAttempts) {
            try {
                return $operation();
            } catch (\Exception $e) {
                $lastException = $e;
                
                if (!$this->isRetryableError($e) || $attempt >= $maxAttempts) {
                    throw $e;
                }

                $delay = $delayCalculator($attempt, $e);
                
                Log::warning('Retry Service: Custom retry', [
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'delay_ms' => $delay,
                    'error' => $e->getMessage()
                ]);

                usleep($delay * 1000);
                $attempt++;
            }
        }

        throw $lastException ?? new \Exception('Custom retry service failed unexpectedly');
    }

    /**
     * Esegue operazioni in parallelo con retry
     */
    public function executeParallelWithRetry(array $operations, array $options = []): array
    {
        $maxConcurrent = $options['max_concurrent'] ?? 5;
        $results = [];
        $errors = [];

        // Esegui le operazioni in batch
        $batches = array_chunk($operations, $maxConcurrent, true);
        
        foreach ($batches as $batch) {
            $batchResults = [];
            $batchErrors = [];

            foreach ($batch as $key => $operation) {
                try {
                    $batchResults[$key] = $this->executeWithRetry($operation, $options);
                } catch (\Exception $e) {
                    $batchErrors[$key] = $e;
                }
            }

            $results = array_merge($results, $batchResults);
            $errors = array_merge($errors, $batchErrors);
        }

        return [
            'results' => $results,
            'errors' => $errors,
            'success_count' => count($results),
            'error_count' => count($errors)
        ];
    }

    /**
     * Esegue un'operazione con timeout
     */
    public function executeWithTimeout(callable $operation, int $timeoutSeconds, array $options = []): mixed
    {
        $startTime = microtime(true);
        $timeout = $timeoutSeconds * 1000000; // Converti in microsecondi

        $operationWithTimeout = function() use ($operation, $startTime, $timeout) {
            if ((microtime(true) - $startTime) * 1000000 > $timeout) {
                throw new \Exception('Operation timeout exceeded');
            }
            return $operation();
        };

        return $this->executeWithRetry($operationWithTimeout, $options);
    }

    /**
     * Esegue un'operazione con circuit breaker
     */
    public function executeWithCircuitBreaker(callable $operation, string $provider, array $options = []): mixed
    {
        $circuitBreaker = app(CircuitBreakerService::class);
        
        // Verifica se il circuit breaker è aperto
        if ($circuitBreaker->isOpen($provider)) {
            throw new \Exception("Circuit breaker is open for provider: {$provider}");
        }

        try {
            $result = $this->executeWithRetry($operation, $options);
            $circuitBreaker->recordSuccess($provider);
            return $result;
        } catch (\Exception $e) {
            $circuitBreaker->recordFailure($provider);
            throw $e;
        }
    }

    /**
     * Ottiene le statistiche di retry
     */
    public function getRetryStatistics(): array
    {
        $cacheKey = 'retry_statistics';
        $stats = Cache::get($cacheKey, [
            'total_operations' => 0,
            'successful_operations' => 0,
            'failed_operations' => 0,
            'retry_operations' => 0,
            'total_retry_attempts' => 0,
            'average_retry_attempts' => 0,
            'retry_success_rate' => 0
        ]);

        return $stats;
    }

    /**
     * Aggiorna le statistiche di retry
     */
    public function updateRetryStatistics(string $operation, bool $success, int $attempts): void
    {
        $cacheKey = 'retry_statistics';
        $stats = Cache::get($cacheKey, [
            'total_operations' => 0,
            'successful_operations' => 0,
            'failed_operations' => 0,
            'retry_operations' => 0,
            'total_retry_attempts' => 0,
            'average_retry_attempts' => 0,
            'retry_success_rate' => 0
        ]);

        $stats['total_operations']++;
        $stats['total_retry_attempts'] += $attempts;

        if ($success) {
            $stats['successful_operations']++;
        } else {
            $stats['failed_operations']++;
        }

        if ($attempts > 1) {
            $stats['retry_operations']++;
        }

        $stats['average_retry_attempts'] = $stats['total_operations'] > 0 ? 
            round($stats['total_retry_attempts'] / $stats['total_operations'], 2) : 0;

        $stats['retry_success_rate'] = $stats['retry_operations'] > 0 ? 
            round(($stats['successful_operations'] / $stats['retry_operations']) * 100, 2) : 0;

        Cache::put($cacheKey, $stats, 3600); // Cache per 1 ora
    }

    /**
     * Reset delle statistiche di retry
     */
    public function resetRetryStatistics(): void
    {
        Cache::forget('retry_statistics');
        
        Log::info('Retry Service: Statistics reset');
    }

    /**
     * Ottiene la configurazione di retry
     */
    public function getRetryConfig(): array
    {
        return $this->config['retry'] ?? [];
    }

    /**
     * Verifica se il retry è abilitato
     */
    public function isEnabled(): bool
    {
        return $this->config['retry']['enabled'] ?? false;
    }

    /**
     * Abilita/disabilita il retry
     */
    public function setEnabled(bool $enabled): void
    {
        $this->config['retry']['enabled'] = $enabled;
        
        Log::info('Retry Service: Enabled status changed', [
            'enabled' => $enabled
        ]);
    }

    /**
     * Ottiene i tipi di errori retryable
     */
    public function getRetryableErrors(): array
    {
        return $this->config['retry']['retryable_errors'] ?? [];
    }

    /**
     * Aggiunge un tipo di errore retryable
     */
    public function addRetryableError(string $errorPattern): void
    {
        $retryableErrors = $this->config['retry']['retryable_errors'] ?? [];
        
        if (!in_array($errorPattern, $retryableErrors)) {
            $retryableErrors[] = $errorPattern;
            $this->config['retry']['retryable_errors'] = $retryableErrors;
            
            Log::info('Retry Service: Added retryable error pattern', [
                'pattern' => $errorPattern
            ]);
        }
    }

    /**
     * Rimuove un tipo di errore retryable
     */
    public function removeRetryableError(string $errorPattern): void
    {
        $retryableErrors = $this->config['retry']['retryable_errors'] ?? [];
        $key = array_search($errorPattern, $retryableErrors);
        
        if ($key !== false) {
            unset($retryableErrors[$key]);
            $this->config['retry']['retryable_errors'] = array_values($retryableErrors);
            
            Log::info('Retry Service: Removed retryable error pattern', [
                'pattern' => $errorPattern
            ]);
        }
    }
}
