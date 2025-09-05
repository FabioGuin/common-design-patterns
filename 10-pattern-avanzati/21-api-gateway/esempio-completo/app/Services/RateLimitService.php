<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RateLimitService
{
    protected $serviceId = 'rate-limit-service';
    protected $version = '1.0.0';

    /**
     * Verifica il rate limit per una richiesta
     */
    public function checkRateLimit(Request $request, ?array $user): array
    {
        try {
            $identifier = $this->getIdentifier($request, $user);
            $limit = $this->getRateLimit($request, $user);
            $window = $this->getTimeWindow($request, $user);

            $key = $this->generateCacheKey($identifier, $request->path(), $window);
            $current = $this->getCurrentCount($key);
            $remaining = max(0, $limit - $current);

            if ($current >= $limit) {
                return [
                    'success' => false,
                    'error' => 'Rate limit exceeded',
                    'limit' => $limit,
                    'remaining' => $remaining,
                    'reset_time' => $this->getResetTime($key, $window)
                ];
            }

            // Incrementa il contatore
            $this->incrementCount($key, $window);

            return [
                'success' => true,
                'limit' => $limit,
                'remaining' => $remaining - 1,
                'reset_time' => $this->getResetTime($key, $window)
            ];

        } catch (\Exception $e) {
            Log::error("Rate Limit Service: Errore nel controllo rate limit", [
                'error' => $e->getMessage(),
                'request_path' => $request->path(),
                'user_id' => $user['id'] ?? null,
                'service' => $this->serviceId
            ]);

            // In caso di errore, permette la richiesta
            return [
                'success' => true,
                'limit' => 1000,
                'remaining' => 999,
                'reset_time' => now()->addMinutes(1)->toISOString()
            ];
        }
    }

    /**
     * Ottiene l'identificatore per il rate limiting
     */
    private function getIdentifier(Request $request, ?array $user): string
    {
        if ($user) {
            return 'user:' . $user['id'];
        }

        $ip = $request->ip();
        $userAgent = $request->userAgent();
        
        return 'ip:' . $ip . ':' . md5($userAgent);
    }

    /**
     * Ottiene il limite di rate per la richiesta
     */
    private function getRateLimit(Request $request, ?array $user): int
    {
        $path = $request->path();
        $method = $request->method();

        // Limiti basati su utente
        if ($user) {
            $role = $user['role'] ?? 'user';
            
            $roleLimits = [
                'admin' => 1000,
                'user' => 100,
                'guest' => 10
            ];

            $baseLimit = $roleLimits[$role] ?? 10;

            // Limiti specifici per endpoint
            $endpointLimits = [
                'api/v1/users' => ['POST' => 10, 'PUT' => 50, 'DELETE' => 5],
                'api/v1/products' => ['POST' => 20, 'PUT' => 50, 'DELETE' => 10],
                'api/v1/orders' => ['POST' => 30, 'PUT' => 100],
                'api/v1/payments' => ['POST' => 20]
            ];

            foreach ($endpointLimits as $endpoint => $limits) {
                if (str_starts_with($path, $endpoint) && isset($limits[$method])) {
                    return min($baseLimit, $limits[$method]);
                }
            }

            return $baseLimit;
        }

        // Limiti per utenti anonimi
        $anonymousLimits = [
            'api/v1/gateway/health' => 1000,
            'api/v1/gateway/stats' => 100,
            'api/v1/gateway/services' => 100,
            'api/v1/products' => 50
        ];

        foreach ($anonymousLimits as $endpoint => $limit) {
            if (str_starts_with($path, $endpoint)) {
                return $limit;
            }
        }

        return 10; // Limite di default
    }

    /**
     * Ottiene la finestra temporale per il rate limiting
     */
    private function getTimeWindow(Request $request, ?array $user): int
    {
        $path = $request->path();

        // Finestre temporali specifiche per endpoint
        $endpointWindows = [
            'api/v1/payments' => 300, // 5 minuti
            'api/v1/users' => 60,     // 1 minuto
            'api/v1/orders' => 60,    // 1 minuto
        ];

        foreach ($endpointWindows as $endpoint => $window) {
            if (str_starts_with($path, $endpoint)) {
                return $window;
            }
        }

        return 60; // 1 minuto di default
    }

    /**
     * Genera una chiave di cache per il rate limiting
     */
    private function generateCacheKey(string $identifier, string $path, int $window): string
    {
        $timeSlot = floor(time() / $window);
        return 'rate_limit:' . $identifier . ':' . $path . ':' . $timeSlot;
    }

    /**
     * Ottiene il conteggio corrente
     */
    private function getCurrentCount(string $key): int
    {
        return Cache::get($key, 0);
    }

    /**
     * Incrementa il conteggio
     */
    private function incrementCount(string $key, int $window): void
    {
        $current = Cache::get($key, 0);
        Cache::put($key, $current + 1, $window);
    }

    /**
     * Ottiene il tempo di reset
     */
    private function getResetTime(string $key, int $window): string
    {
        $timeSlot = (int) explode(':', $key)[3];
        $resetTime = ($timeSlot + 1) * $window;
        
        return now()->setTimestamp($resetTime)->toISOString();
    }

    /**
     * Ottiene le informazioni sul rate limit
     */
    public function getRateLimitInfo(Request $request, ?array $user): array
    {
        try {
            $identifier = $this->getIdentifier($request, $user);
            $limit = $this->getRateLimit($request, $user);
            $window = $this->getTimeWindow($request, $user);

            $key = $this->generateCacheKey($identifier, $request->path(), $window);
            $current = $this->getCurrentCount($key);
            $remaining = max(0, $limit - $current);

            return [
                'success' => true,
                'limit' => $limit,
                'current' => $current,
                'remaining' => $remaining,
                'reset_time' => $this->getResetTime($key, $window),
                'window' => $window
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Resetta il rate limit per un identificatore
     */
    public function resetRateLimit(string $identifier, string $path): array
    {
        try {
            $windows = [60, 300, 3600]; // 1 min, 5 min, 1 ora
            
            foreach ($windows as $window) {
                $key = $this->generateCacheKey($identifier, $path, $window);
                Cache::forget($key);
            }

            return [
                'success' => true,
                'message' => 'Rate limit reset successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Ottiene le statistiche del rate limiting
     */
    public function getRateLimitStats(): array
    {
        try {
            $stats = [
                'total_requests' => 0,
                'blocked_requests' => 0,
                'active_identifiers' => 0,
                'top_endpoints' => [],
                'top_identifiers' => []
            ];

            // Simula statistiche
            $stats['total_requests'] = rand(1000, 10000);
            $stats['blocked_requests'] = rand(50, 500);
            $stats['active_identifiers'] = rand(100, 1000);
            $stats['top_endpoints'] = [
                ['endpoint' => 'api/v1/products', 'requests' => rand(100, 1000)],
                ['endpoint' => 'api/v1/users', 'requests' => rand(50, 500)],
                ['endpoint' => 'api/v1/orders', 'requests' => rand(25, 250)]
            ];
            $stats['top_identifiers'] = [
                ['identifier' => 'user:123', 'requests' => rand(10, 100)],
                ['identifier' => 'ip:192.168.1.1', 'requests' => rand(5, 50)]
            ];

            return [
                'success' => true,
                'data' => $stats,
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Configura il rate limit per un utente
     */
    public function configureUserRateLimit(string $userId, int $limit, int $window): array
    {
        try {
            $key = 'user_rate_limit:' . $userId;
            $config = [
                'limit' => $limit,
                'window' => $window,
                'updated_at' => now()->toISOString()
            ];

            Cache::put($key, $config, 86400); // 24 ore

            return [
                'success' => true,
                'message' => 'Rate limit configured successfully',
                'config' => $config
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Ottiene la configurazione del rate limit per un utente
     */
    public function getUserRateLimitConfig(string $userId): array
    {
        try {
            $key = 'user_rate_limit:' . $userId;
            $config = Cache::get($key);

            if (!$config) {
                return [
                    'success' => false,
                    'error' => 'Rate limit configuration not found'
                ];
            }

            return [
                'success' => true,
                'config' => $config
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Health check del servizio
     */
    public function healthCheck(): array
    {
        try {
            return [
                'success' => true,
                'status' => 'healthy',
                'service' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'service' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->serviceId;
    }

    /**
     * Ottiene la versione del servizio
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
