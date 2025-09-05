<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CachingService
{
    protected $serviceId = 'caching-service';
    protected $version = '1.0.0';

    /**
     * Ottiene un valore dalla cache
     */
    public function get(string $key): mixed
    {
        try {
            return Cache::get($key);

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nel recupero cache", [
                'error' => $e->getMessage(),
                'key' => $key,
                'service' => $this->serviceId
            ]);

            return null;
        }
    }

    /**
     * Memorizza un valore nella cache
     */
    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        try {
            Cache::put($key, $value, $ttl);
            return true;

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nel salvataggio cache", [
                'error' => $e->getMessage(),
                'key' => $key,
                'service' => $this->serviceId
            ]);

            return false;
        }
    }

    /**
     * Memorizza un valore nella cache se non esiste
     */
    public function putIfNotExists(string $key, mixed $value, int $ttl = 3600): bool
    {
        try {
            if (!Cache::has($key)) {
                Cache::put($key, $value, $ttl);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nel putIfNotExists", [
                'error' => $e->getMessage(),
                'key' => $key,
                'service' => $this->serviceId
            ]);

            return false;
        }
    }

    /**
     * Rimuove un valore dalla cache
     */
    public function forget(string $key): bool
    {
        try {
            Cache::forget($key);
            return true;

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nella rimozione cache", [
                'error' => $e->getMessage(),
                'key' => $key,
                'service' => $this->serviceId
            ]);

            return false;
        }
    }

    /**
     * Verifica se una chiave esiste nella cache
     */
    public function has(string $key): bool
    {
        try {
            return Cache::has($key);

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nella verifica cache", [
                'error' => $e->getMessage(),
                'key' => $key,
                'service' => $this->serviceId
            ]);

            return false;
        }
    }

    /**
     * Ottiene o memorizza un valore usando una callback
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        try {
            return Cache::remember($key, $ttl, $callback);

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nel remember", [
                'error' => $e->getMessage(),
                'key' => $key,
                'service' => $this->serviceId
            ]);

            // Esegui la callback direttamente in caso di errore
            return $callback();
        }
    }

    /**
     * Ottiene o memorizza un valore permanentemente
     */
    public function rememberForever(string $key, callable $callback): mixed
    {
        try {
            return Cache::rememberForever($key, $callback);

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nel rememberForever", [
                'error' => $e->getMessage(),
                'key' => $key,
                'service' => $this->serviceId
            ]);

            // Esegui la callback direttamente in caso di errore
            return $callback();
        }
    }

    /**
     * Incrementa un valore numerico nella cache
     */
    public function increment(string $key, int $value = 1): int
    {
        try {
            return Cache::increment($key, $value);

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nell'incremento", [
                'error' => $e->getMessage(),
                'key' => $key,
                'service' => $this->serviceId
            ]);

            return 0;
        }
    }

    /**
     * Decrementa un valore numerico nella cache
     */
    public function decrement(string $key, int $value = 1): int
    {
        try {
            return Cache::decrement($key, $value);

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nel decremento", [
                'error' => $e->getMessage(),
                'key' => $key,
                'service' => $this->serviceId
            ]);

            return 0;
        }
    }

    /**
     * Pulisce tutta la cache
     */
    public function flush(): bool
    {
        try {
            Cache::flush();
            return true;

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nel flush", [
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ]);

            return false;
        }
    }

    /**
     * Pulisce la cache con un pattern
     */
    public function flushPattern(string $pattern): int
    {
        try {
            $keys = $this->getKeysByPattern($pattern);
            $count = 0;

            foreach ($keys as $key) {
                if (Cache::forget($key)) {
                    $count++;
                }
            }

            return $count;

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nel flushPattern", [
                'error' => $e->getMessage(),
                'pattern' => $pattern,
                'service' => $this->serviceId
            ]);

            return 0;
        }
    }

    /**
     * Ottiene le chiavi che corrispondono a un pattern
     */
    private function getKeysByPattern(string $pattern): array
    {
        try {
            // Simula ricerca per pattern
            $keys = [];
            $pattern = str_replace('*', '.*', $pattern);
            $pattern = '/^' . $pattern . '$/';

            // In un'implementazione reale, useresti Redis SCAN
            // Per ora simuliamo con chiavi note
            $knownKeys = [
                'api_gateway:users:list',
                'api_gateway:products:list',
                'api_gateway:orders:list',
                'api_gateway:payments:list',
                'rate_limit:user:123:api/v1/users:0',
                'rate_limit:ip:192.168.1.1:api/v1/products:0'
            ];

            foreach ($knownKeys as $key) {
                if (preg_match($pattern, $key)) {
                    $keys[] = $key;
                }
            }

            return $keys;

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Ottiene le statistiche della cache
     */
    public function getCacheStats(): array
    {
        try {
            $stats = [
                'total_keys' => 0,
                'memory_usage' => 0,
                'hit_rate' => 0,
                'miss_rate' => 0,
                'top_keys' => [],
                'expired_keys' => 0
            ];

            // Simula statistiche
            $stats['total_keys'] = rand(100, 1000);
            $stats['memory_usage'] = rand(1024, 10240) . ' KB';
            $stats['hit_rate'] = rand(80, 95) . '%';
            $stats['miss_rate'] = rand(5, 20) . '%';
            $stats['expired_keys'] = rand(10, 100);

            $stats['top_keys'] = [
                ['key' => 'api_gateway:users:list', 'hits' => rand(100, 1000)],
                ['key' => 'api_gateway:products:list', 'hits' => rand(50, 500)],
                ['key' => 'api_gateway:orders:list', 'hits' => rand(25, 250)]
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
     * Ottiene informazioni su una chiave specifica
     */
    public function getKeyInfo(string $key): array
    {
        try {
            $exists = Cache::has($key);
            $value = $exists ? Cache::get($key) : null;
            $ttl = $this->getKeyTtl($key);

            return [
                'success' => true,
                'key' => $key,
                'exists' => $exists,
                'value' => $value,
                'ttl' => $ttl,
                'size' => $value ? strlen(serialize($value)) : 0
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'key' => $key
            ];
        }
    }

    /**
     * Ottiene il TTL di una chiave
     */
    private function getKeyTtl(string $key): ?int
    {
        try {
            // Simula recupero TTL
            return rand(60, 3600);

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Imposta il TTL di una chiave
     */
    public function setTtl(string $key, int $ttl): bool
    {
        try {
            $value = Cache::get($key);
            if ($value !== null) {
                Cache::put($key, $value, $ttl);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nel setTtl", [
                'error' => $e->getMessage(),
                'key' => $key,
                'service' => $this->serviceId
            ]);

            return false;
        }
    }

    /**
     * Ottiene tutte le chiavi con un pattern
     */
    public function getKeys(string $pattern = '*'): array
    {
        try {
            return $this->getKeysByPattern($pattern);

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nel getKeys", [
                'error' => $e->getMessage(),
                'pattern' => $pattern,
                'service' => $this->serviceId
            ]);

            return [];
        }
    }

    /**
     * Ottiene il valore di una chiave e la rimuove
     */
    public function pull(string $key): mixed
    {
        try {
            $value = Cache::get($key);
            if ($value !== null) {
                Cache::forget($key);
            }

            return $value;

        } catch (\Exception $e) {
            Log::error("Caching Service: Errore nel pull", [
                'error' => $e->getMessage(),
                'key' => $key,
                'service' => $this->serviceId
            ]);

            return null;
        }
    }

    /**
     * Health check del servizio
     */
    public function healthCheck(): array
    {
        try {
            // Test di base
            $testKey = 'health_check_' . uniqid();
            $testValue = 'test_value';
            
            $putResult = $this->put($testKey, $testValue, 60);
            $getResult = $this->get($testKey);
            $forgetResult = $this->forget($testKey);

            $healthy = $putResult && $getResult === $testValue && $forgetResult;

            return [
                'success' => $healthy,
                'status' => $healthy ? 'healthy' : 'unhealthy',
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
