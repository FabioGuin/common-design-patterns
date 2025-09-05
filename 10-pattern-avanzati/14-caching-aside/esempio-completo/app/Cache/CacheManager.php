<?php

namespace App\Cache;

use App\Models\CacheMetric;
use App\Models\CacheEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CacheManager
{
    private array $cacheConfigs = [];

    public function __construct()
    {
        $this->initializeCacheConfigs();
    }

    public function get(string $key, string $entity, callable $fallback = null): mixed
    {
        $config = $this->getCacheConfig($entity);
        $cacheKey = $this->generateCacheKey($key, $entity);
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            // Controlla cache
            $data = Cache::get($cacheKey);
            
            if ($data !== null) {
                $this->recordCacheHit($entity, $key, microtime(true) - $startTime, memory_get_usage() - $startMemory);
                return $data;
            }

            // Cache miss - carica dal database
            if ($fallback) {
                $data = $fallback();
                
                if ($data !== null) {
                    // Salva in cache
                    Cache::put($cacheKey, $data, $config['ttl']);
                    $this->recordCacheMiss($entity, $key, microtime(true) - $startTime, memory_get_usage() - $startMemory);
                }
                
                return $data;
            }

            $this->recordCacheMiss($entity, $key, microtime(true) - $startTime, memory_get_usage() - $startMemory);
            return null;

        } catch (\Exception $e) {
            $this->recordCacheError($entity, $key, microtime(true) - $startTime, memory_get_usage() - $startMemory, $e);
            throw $e;
        }
    }

    public function put(string $key, string $entity, mixed $data, string $strategy = 'write_through'): bool
    {
        $config = $this->getCacheConfig($entity);
        $cacheKey = $this->generateCacheKey($key, $entity);
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        try {
            switch ($strategy) {
                case 'write_through':
                    return $this->writeThrough($cacheKey, $data, $config, $entity, $key, $startTime, $startMemory);
                case 'write_behind':
                    return $this->writeBehind($cacheKey, $data, $config, $entity, $key, $startTime, $startMemory);
                case 'write_around':
                    return $this->writeAround($cacheKey, $data, $config, $entity, $key, $startTime, $startMemory);
                default:
                    throw new \InvalidArgumentException("Unknown cache strategy: {$strategy}");
            }
        } catch (\Exception $e) {
            $this->recordCacheError($entity, $key, microtime(true) - $startTime, memory_get_usage() - $startMemory, $e);
            throw $e;
        }
    }

    public function forget(string $key, string $entity): bool
    {
        $cacheKey = $this->generateCacheKey($key, $entity);
        
        try {
            $result = Cache::forget($cacheKey);
            $this->recordCacheInvalidation($entity, $key);
            return $result;
        } catch (\Exception $e) {
            Log::error("Cache invalidation failed for {$entity}:{$key}: " . $e->getMessage());
            return false;
        }
    }

    public function refresh(string $key, string $entity, callable $fallback = null): mixed
    {
        // Invalida cache esistente
        $this->forget($key, $entity);
        
        // Ricarica dati
        return $this->get($key, $entity, $fallback);
    }

    public function preload(string $entity, callable $fallback): array
    {
        $config = $this->getCacheConfig($entity);
        $data = $fallback();
        
        if (!is_array($data)) {
            return [];
        }

        $preloaded = [];
        foreach ($data as $item) {
            $key = $this->extractKey($item, $entity);
            $cacheKey = $this->generateCacheKey($key, $entity);
            
            Cache::put($cacheKey, $item, $config['ttl']);
            $preloaded[] = $key;
        }

        return $preloaded;
    }

    public function getCacheStats(string $entity): array
    {
        $metrics = CacheMetric::where('entity', $entity)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $totalOperations = $metrics->sum('total_operations');
        $cacheHits = $metrics->sum('cache_hits');
        $cacheMisses = $metrics->sum('cache_misses');
        $cacheErrors = $metrics->sum('cache_errors');

        return [
            'entity' => $entity,
            'total_operations' => $totalOperations,
            'cache_hits' => $cacheHits,
            'cache_misses' => $cacheMisses,
            'cache_errors' => $cacheErrors,
            'hit_ratio' => $totalOperations > 0 ? ($cacheHits / $totalOperations) * 100 : 0,
            'miss_ratio' => $totalOperations > 0 ? ($cacheMisses / $totalOperations) * 100 : 0,
            'error_ratio' => $totalOperations > 0 ? ($cacheErrors / $totalOperations) * 100 : 0,
            'avg_execution_time' => $metrics->avg('execution_time'),
            'avg_memory_used' => $metrics->avg('memory_used'),
        ];
    }

    private function writeThrough(string $cacheKey, mixed $data, array $config, string $entity, string $key, float $startTime, int $startMemory): bool
    {
        // Aggiorna cache
        Cache::put($cacheKey, $data, $config['ttl']);
        
        // Aggiorna database (simulato)
        $this->updateDatabase($entity, $key, $data);
        
        $this->recordCacheWrite($entity, $key, microtime(true) - $startTime, memory_get_usage() - $startMemory);
        return true;
    }

    private function writeBehind(string $cacheKey, mixed $data, array $config, string $entity, string $key, float $startTime, int $startMemory): bool
    {
        // Aggiorna cache immediatamente
        Cache::put($cacheKey, $data, $config['ttl']);
        
        // Aggiorna database in background
        $this->updateDatabaseAsync($entity, $key, $data);
        
        $this->recordCacheWrite($entity, $key, microtime(true) - $startTime, memory_get_usage() - $startMemory);
        return true;
    }

    private function writeAround(string $cacheKey, mixed $data, array $config, string $entity, string $key, float $startTime, int $startMemory): bool
    {
        // Aggiorna solo database
        $this->updateDatabase($entity, $key, $data);
        
        // Invalida cache
        Cache::forget($cacheKey);
        
        $this->recordCacheWrite($entity, $key, microtime(true) - $startTime, memory_get_usage() - $startMemory);
        return true;
    }

    private function updateDatabase(string $entity, string $key, mixed $data): void
    {
        // Simula aggiornamento database
        // In un'implementazione reale, qui aggiorneresti il database
        Log::info("Database updated for {$entity}:{$key}");
    }

    private function updateDatabaseAsync(string $entity, string $key, mixed $data): void
    {
        // Simula aggiornamento database asincrono
        // In un'implementazione reale, useresti job in coda
        Log::info("Database update queued for {$entity}:{$key}");
    }

    private function generateCacheKey(string $key, string $entity): string
    {
        return "{$entity}:{$key}";
    }

    private function extractKey(mixed $item, string $entity): string
    {
        if (is_array($item) && isset($item['id'])) {
            return (string) $item['id'];
        }
        
        if (is_object($item) && isset($item->id)) {
            return (string) $item->id;
        }
        
        return md5(serialize($item));
    }

    private function getCacheConfig(string $entity): array
    {
        return $this->cacheConfigs[$entity] ?? $this->cacheConfigs['default'];
    }

    private function recordCacheHit(string $entity, string $key, float $executionTime, int $memoryUsed): void
    {
        if (!config('cache.monitoring.enabled', true)) {
            return;
        }

        CacheMetric::create([
            'entity' => $entity,
            'key' => $key,
            'operation' => 'hit',
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'total_operations' => 1,
            'cache_hits' => 1,
            'cache_misses' => 0,
            'cache_errors' => 0,
            'created_at' => now(),
        ]);
    }

    private function recordCacheMiss(string $entity, string $key, float $executionTime, int $memoryUsed): void
    {
        if (!config('cache.monitoring.enabled', true)) {
            return;
        }

        CacheMetric::create([
            'entity' => $entity,
            'key' => $key,
            'operation' => 'miss',
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'total_operations' => 1,
            'cache_hits' => 0,
            'cache_misses' => 1,
            'cache_errors' => 0,
            'created_at' => now(),
        ]);
    }

    private function recordCacheWrite(string $entity, string $key, float $executionTime, int $memoryUsed): void
    {
        if (!config('cache.monitoring.enabled', true)) {
            return;
        }

        CacheMetric::create([
            'entity' => $entity,
            'key' => $key,
            'operation' => 'write',
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'total_operations' => 1,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'cache_errors' => 0,
            'created_at' => now(),
        ]);
    }

    private function recordCacheError(string $entity, string $key, float $executionTime, int $memoryUsed, \Exception $e): void
    {
        if (!config('cache.monitoring.enabled', true)) {
            return;
        }

        CacheMetric::create([
            'entity' => $entity,
            'key' => $key,
            'operation' => 'error',
            'execution_time' => $executionTime,
            'memory_used' => $memoryUsed,
            'total_operations' => 1,
            'cache_hits' => 0,
            'cache_misses' => 0,
            'cache_errors' => 1,
            'created_at' => now(),
        ]);

        CacheEvent::create([
            'entity' => $entity,
            'key' => $key,
            'event_type' => 'error',
            'error_message' => $e->getMessage(),
            'created_at' => now(),
        ]);

        Log::error("Cache error for {$entity}:{$key}: " . $e->getMessage());
    }

    private function recordCacheInvalidation(string $entity, string $key): void
    {
        if (!config('cache.monitoring.enabled', true)) {
            return;
        }

        CacheEvent::create([
            'entity' => $entity,
            'key' => $key,
            'event_type' => 'invalidation',
            'error_message' => null,
            'created_at' => now(),
        ]);
    }

    private function initializeCacheConfigs(): void
    {
        $configs = config('cache.entities', []);

        foreach ($configs as $entity => $config) {
            $this->cacheConfigs[$entity] = $config;
        }
    }
}
