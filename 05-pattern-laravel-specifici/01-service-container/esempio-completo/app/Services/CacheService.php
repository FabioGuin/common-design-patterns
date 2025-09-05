<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    public function __construct(
        private \Illuminate\Contracts\Cache\Repository $cache
    ) {}

    /**
     * Recupera un valore dalla cache
     */
    public function get(string $key, $default = null)
    {
        $value = $this->cache->get($key, $default);
        
        Log::debug('CacheService: Retrieved from cache', [
            'key' => $key,
            'found' => $value !== $default
        ]);

        return $value;
    }

    /**
     * Memorizza un valore nella cache
     */
    public function put(string $key, $value, int $ttl = 3600): bool
    {
        $result = $this->cache->put($key, $value, $ttl);
        
        Log::debug('CacheService: Stored in cache', [
            'key' => $key,
            'ttl' => $ttl,
            'success' => $result
        ]);

        return $result;
    }

    /**
     * Memorizza un valore nella cache se non esiste
     */
    public function add(string $key, $value, int $ttl = 3600): bool
    {
        $result = $this->cache->add($key, $value, $ttl);
        
        Log::debug('CacheService: Added to cache', [
            'key' => $key,
            'ttl' => $ttl,
            'success' => $result
        ]);

        return $result;
    }

    /**
     * Memorizza un valore nella cache per sempre
     */
    public function forever(string $key, $value): bool
    {
        $result = $this->cache->forever($key, $value);
        
        Log::debug('CacheService: Stored forever in cache', [
            'key' => $key,
            'success' => $result
        ]);

        return $result;
    }

    /**
     * Recupera un valore dalla cache o esegue una closure
     */
    public function remember(string $key, int $ttl, callable $callback)
    {
        return $this->cache->remember($key, $ttl, function () use ($callback, $key) {
            Log::debug('CacheService: Executing callback for cache', ['key' => $key]);
            return $callback();
        });
    }

    /**
     * Recupera un valore dalla cache o esegue una closure (per sempre)
     */
    public function rememberForever(string $key, callable $callback)
    {
        return $this->cache->rememberForever($key, function () use ($callback, $key) {
            Log::debug('CacheService: Executing callback for forever cache', ['key' => $key]);
            return $callback();
        });
    }

    /**
     * Rimuove un valore dalla cache
     */
    public function forget(string $key): bool
    {
        $result = $this->cache->forget($key);
        
        Log::debug('CacheService: Removed from cache', [
            'key' => $key,
            'success' => $result
        ]);

        return $result;
    }

    /**
     * Rimuove più valori dalla cache usando pattern
     */
    public function forgetPattern(string $pattern): int
    {
        $keys = $this->getKeysByPattern($pattern);
        $removedCount = 0;

        foreach ($keys as $key) {
            if ($this->forget($key)) {
                $removedCount++;
            }
        }

        Log::debug('CacheService: Removed pattern from cache', [
            'pattern' => $pattern,
            'removed_count' => $removedCount
        ]);

        return $removedCount;
    }

    /**
     * Pulisce tutta la cache
     */
    public function flush(): bool
    {
        $result = $this->cache->flush();
        
        Log::info('CacheService: Flushed all cache', ['success' => $result]);

        return $result;
    }

    /**
     * Verifica se una chiave esiste nella cache
     */
    public function has(string $key): bool
    {
        $exists = $this->cache->has($key);
        
        Log::debug('CacheService: Checked cache key existence', [
            'key' => $key,
            'exists' => $exists
        ]);

        return $exists;
    }

    /**
     * Incrementa un valore nella cache
     */
    public function increment(string $key, int $value = 1): int
    {
        $result = $this->cache->increment($key, $value);
        
        Log::debug('CacheService: Incremented cache value', [
            'key' => $key,
            'value' => $value,
            'result' => $result
        ]);

        return $result;
    }

    /**
     * Decrementa un valore nella cache
     */
    public function decrement(string $key, int $value = 1): int
    {
        $result = $this->cache->decrement($key, $value);
        
        Log::debug('CacheService: Decremented cache value', [
            'key' => $key,
            'value' => $value,
            'result' => $result
        ]);

        return $result;
    }

    /**
     * Ottiene le chiavi che corrispondono a un pattern
     */
    private function getKeysByPattern(string $pattern): array
    {
        // Implementazione semplificata per pattern matching
        // In un'implementazione reale, questo dipenderebbe dal driver di cache
        $keys = [];
        
        // Per Redis, potresti usare SCAN
        // Per altri driver, potresti dover implementare una logica diversa
        
        return $keys;
    }

    /**
     * Ottiene statistiche della cache
     */
    public function getStats(): array
    {
        return [
            'driver' => $this->cache->getStore()->getDriver(),
            'prefix' => $this->cache->getPrefix(),
            'is_connected' => $this->isConnected()
        ];
    }

    /**
     * Verifica se la cache è connessa
     */
    public function isConnected(): bool
    {
        try {
            $this->cache->put('test_connection', 'test', 1);
            $this->cache->forget('test_connection');
            return true;
        } catch (\Exception $e) {
            Log::error('CacheService: Connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Ottiene informazioni sulla cache
     */
    public function getInfo(): array
    {
        return [
            'driver' => config('cache.default'),
            'is_connected' => $this->isConnected(),
            'prefix' => $this->cache->getPrefix(),
            'stats' => $this->getStats()
        ];
    }
}
