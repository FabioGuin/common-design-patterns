<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    private array $defaultTTL = [
        'user_data' => 300, // 5 minuti
        'product_data' => 3600, // 1 ora
        'category_data' => 7200, // 2 ore
        'static_data' => 86400, // 24 ore
        'api_response' => 1800, // 30 minuti
        'view_cache' => 3600, // 1 ora
    ];

    /**
     * Get cached data or execute callback and cache result
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->getTTL($key);
        
        return Cache::remember($key, $ttl, function () use ($callback, $key) {
            Log::info('Cache miss', ['key' => $key]);
            return $callback();
        });
    }

    /**
     * Get cached data or execute callback and cache result with tags
     */
    public function rememberWithTags(string $key, array $tags, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->getTTL($key);
        
        return Cache::tags($tags)->remember($key, $ttl, function () use ($callback, $key) {
            Log::info('Cache miss with tags', ['key' => $key, 'tags' => $tags]);
            return $callback();
        });
    }

    /**
     * Store data in cache
     */
    public function put(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->getTTL($key);
        
        Log::info('Cache put', ['key' => $key, 'ttl' => $ttl]);
        return Cache::put($key, $value, $ttl);
    }

    /**
     * Store data in cache with tags
     */
    public function putWithTags(string $key, mixed $value, array $tags, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->getTTL($key);
        
        Log::info('Cache put with tags', ['key' => $key, 'tags' => $tags, 'ttl' => $ttl]);
        return Cache::tags($tags)->put($key, $value, $ttl);
    }

    /**
     * Get data from cache
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::get($key, $default);
        
        if ($value !== $default) {
            Log::info('Cache hit', ['key' => $key]);
        } else {
            Log::info('Cache miss', ['key' => $key]);
        }
        
        return $value;
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    /**
     * Remove data from cache
     */
    public function forget(string $key): bool
    {
        Log::info('Cache forget', ['key' => $key]);
        return Cache::forget($key);
    }

    /**
     * Remove data from cache with tags
     */
    public function forgetWithTags(array $tags): bool
    {
        Log::info('Cache forget with tags', ['tags' => $tags]);
        return Cache::tags($tags)->flush();
    }

    /**
     * Clear all cache
     */
    public function flush(): bool
    {
        Log::info('Cache flush');
        return Cache::flush();
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'store' => config('cache.stores.' . config('cache.default') . '.driver'),
            'prefix' => config('cache.prefix'),
            'default_ttl' => $this->defaultTTL,
        ];
    }

    /**
     * Get TTL for key
     */
    private function getTTL(string $key): int
    {
        foreach ($this->defaultTTL as $pattern => $ttl) {
            if (str_contains($key, $pattern)) {
                return $ttl;
            }
        }
        
        return 60; // Default 1 minute
    }

    /**
     * Generate cache key
     */
    public function generateKey(string $prefix, array $params = []): string
    {
        $key = $prefix;
        
        if (!empty($params)) {
            $key .= ':' . md5(serialize($params));
        }
        
        return $key;
    }

    /**
     * Cache query result
     */
    public function cacheQuery(string $query, callable $callback, ?int $ttl = null): mixed
    {
        $key = $this->generateKey('query', ['sql' => $query]);
        return $this->remember($key, $callback, $ttl);
    }

    /**
     * Cache model data
     */
    public function cacheModel(string $model, int $id, callable $callback, ?int $ttl = null): mixed
    {
        $key = $this->generateKey('model', ['model' => $model, 'id' => $id]);
        return $this->remember($key, $callback, $ttl);
    }

    /**
     * Cache API response
     */
    public function cacheApiResponse(string $endpoint, array $params, callable $callback, ?int $ttl = null): mixed
    {
        $key = $this->generateKey('api', ['endpoint' => $endpoint, 'params' => $params]);
        return $this->remember($key, $callback, $ttl);
    }
}
