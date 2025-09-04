<?php

namespace App\Services\AI\Strategies;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LRUCacheStrategy implements CacheStrategyInterface
{
    private array $config;
    private array $accessOrder = [];
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'puts' => 0,
        'forgets' => 0,
        'flushes' => 0,
        'evictions' => 0
    ];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function put(string $key, array $data, int $ttl): bool
    {
        try {
            $maxSize = $this->config['max_size'] ?? 1000;
            
            // Se la cache è piena, rimuovi l'elemento meno recentemente utilizzato
            if ($this->isFull() && !$this->has($key)) {
                $this->evictLeastRecentlyUsed();
            }

            $success = Cache::put($key, $data, $ttl);
            
            if ($success) {
                $this->updateAccessOrder($key);
                $this->stats['puts']++;
                
                Log::debug('LRU Cache: Data stored', [
                    'key' => $key,
                    'ttl' => $ttl,
                    'cache_size' => $this->getCurrentSize()
                ]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('LRU Cache: Failed to store data', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function get(string $key): ?array
    {
        try {
            $data = Cache::get($key);
            
            if ($data !== null) {
                $this->updateAccessOrder($key);
                $this->stats['hits']++;
                
                Log::debug('LRU Cache: Data retrieved', ['key' => $key]);
                
                return $data;
            }

            $this->stats['misses']++;
            return null;

        } catch (\Exception $e) {
            Log::error('LRU Cache: Failed to retrieve data', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    public function forget(string $key): bool
    {
        try {
            $success = Cache::forget($key);
            
            if ($success) {
                $this->removeFromAccessOrder($key);
                $this->stats['forgets']++;
                
                Log::debug('LRU Cache: Data removed', ['key' => $key]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('LRU Cache: Failed to remove data', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function flush(): bool
    {
        try {
            $success = Cache::flush();
            
            if ($success) {
                $this->accessOrder = [];
                $this->stats['flushes']++;
                
                Log::info('LRU Cache: Flushed');
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('LRU Cache: Failed to flush', [
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    public function getKeys(string $pattern = '*', int $limit = 100): array
    {
        // Implementazione semplificata - in produzione usare Redis SCAN
        return array_slice($this->accessOrder, 0, $limit);
    }

    public function getStats(): array
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        $hitRate = $total > 0 ? ($this->stats['hits'] / $total) * 100 : 0;
        $missRate = $total > 0 ? ($this->stats['misses'] / $total) * 100 : 0;

        return [
            'strategy' => 'lru',
            'hits' => $this->stats['hits'],
            'misses' => $this->stats['misses'],
            'hit_rate' => round($hitRate, 2),
            'miss_rate' => round($missRate, 2),
            'puts' => $this->stats['puts'],
            'forgets' => $this->stats['forgets'],
            'flushes' => $this->stats['flushes'],
            'evictions' => $this->stats['evictions'],
            'current_size' => $this->getCurrentSize(),
            'max_size' => $this->getMaxSize(),
            'utilization' => round(($this->getCurrentSize() / $this->getMaxSize()) * 100, 2)
        ];
    }

    public function optimize(): array
    {
        $beforeSize = $this->getCurrentSize();
        $evicted = $this->cleanExpired();
        $afterSize = $this->getCurrentSize();

        return [
            'entries_removed' => $evicted,
            'memory_freed' => $beforeSize - $afterSize,
            'optimization_successful' => true
        ];
    }

    public function healthCheck(): array
    {
        try {
            // Test di scrittura e lettura
            $testKey = 'health_check_' . time();
            $testData = ['test' => true, 'timestamp' => time()];
            
            $writeSuccess = $this->put($testKey, $testData, 60);
            $readData = $this->get($testKey);
            $deleteSuccess = $this->forget($testKey);
            
            $healthy = $writeSuccess && $readData !== null && $deleteSuccess;
            
            return [
                'healthy' => $healthy,
                'write_test' => $writeSuccess,
                'read_test' => $readData !== null,
                'delete_test' => $deleteSuccess,
                'current_size' => $this->getCurrentSize(),
                'max_size' => $this->getMaxSize(),
                'utilization' => round(($this->getCurrentSize() / $this->getMaxSize()) * 100, 2)
            ];

        } catch (\Exception $e) {
            return [
                'healthy' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getName(): string
    {
        return 'lru';
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function updateConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    public function getMaxSize(): int
    {
        return $this->config['max_size'] ?? 1000;
    }

    public function getCurrentSize(): int
    {
        return count($this->accessOrder);
    }

    public function isFull(): bool
    {
        return $this->getCurrentSize() >= $this->getMaxSize();
    }

    public function getTtl(string $key): ?int
    {
        // Implementazione semplificata - in produzione usare Redis TTL
        return $this->has($key) ? 3600 : null;
    }

    public function updateTtl(string $key, int $ttl): bool
    {
        $data = $this->get($key);
        if ($data === null) {
            return false;
        }

        return $this->put($key, $data, $ttl);
    }

    public function getExpiringKeys(int $minutes = 5): array
    {
        // Implementazione semplificata - in produzione usare Redis TTL
        return [];
    }

    public function cleanExpired(): int
    {
        // Implementazione semplificata - in produzione usare Redis TTL
        return 0;
    }

    public function getMostUsedKeys(int $limit = 10): array
    {
        return array_slice($this->accessOrder, 0, $limit);
    }

    public function getLeastUsedKeys(int $limit = 10): array
    {
        return array_slice(array_reverse($this->accessOrder), 0, $limit);
    }

    public function getLargestKeys(int $limit = 10): array
    {
        // Implementazione semplificata - in produzione usare Redis MEMORY USAGE
        return array_slice($this->accessOrder, 0, $limit);
    }

    public function getSmallestKeys(int $limit = 10): array
    {
        // Implementazione semplificata - in produzione usare Redis MEMORY USAGE
        return array_slice(array_reverse($this->accessOrder), 0, $limit);
    }

    public function getUsageStats(): array
    {
        return [
            'total_operations' => $this->stats['hits'] + $this->stats['misses'] + $this->stats['puts'],
            'hits' => $this->stats['hits'],
            'misses' => $this->stats['misses'],
            'puts' => $this->stats['puts'],
            'forgets' => $this->stats['forgets'],
            'evictions' => $this->stats['evictions']
        ];
    }

    public function getPerformanceStats(): array
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        
        return [
            'hit_rate' => $total > 0 ? round(($this->stats['hits'] / $total) * 100, 2) : 0,
            'miss_rate' => $total > 0 ? round(($this->stats['misses'] / $total) * 100, 2) : 0,
            'efficiency' => $this->getCurrentSize() / $this->getMaxSize(),
            'eviction_rate' => $this->stats['puts'] > 0 ? round(($this->stats['evictions'] / $this->stats['puts']) * 100, 2) : 0
        ];
    }

    public function getMemoryStats(): array
    {
        return [
            'current_size' => $this->getCurrentSize(),
            'max_size' => $this->getMaxSize(),
            'utilization' => round(($this->getCurrentSize() / $this->getMaxSize()) * 100, 2),
            'available' => $this->getMaxSize() - $this->getCurrentSize()
        ];
    }

    public function getHitRateStats(): array
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        
        return [
            'hit_rate' => $total > 0 ? round(($this->stats['hits'] / $total) * 100, 2) : 0,
            'hits' => $this->stats['hits'],
            'total_requests' => $total
        ];
    }

    public function getMissRateStats(): array
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        
        return [
            'miss_rate' => $total > 0 ? round(($this->stats['misses'] / $total) * 100, 2) : 0,
            'misses' => $this->stats['misses'],
            'total_requests' => $total
        ];
    }

    public function getTtlStats(): array
    {
        return [
            'default_ttl' => $this->config['ttl'] ?? 3600,
            'max_ttl' => $this->config['max_ttl'] ?? 86400
        ];
    }

    public function getCompressionStats(): array
    {
        return [
            'compression_enabled' => false,
            'compression_ratio' => 0,
            'compressed_entries' => 0
        ];
    }

    public function getEncryptionStats(): array
    {
        return [
            'encryption_enabled' => false,
            'encrypted_entries' => 0
        ];
    }

    public function getTagStats(): array
    {
        return [
            'tags_enabled' => false,
            'total_tags' => 0,
            'tagged_entries' => 0
        ];
    }

    public function getPatternStats(): array
    {
        return [
            'pattern_matching_enabled' => false,
            'total_patterns' => 0
        ];
    }

    public function getStrategyStats(): array
    {
        return [
            'strategy' => 'lru',
            'description' => 'Least Recently Used',
            'max_size' => $this->getMaxSize(),
            'current_size' => $this->getCurrentSize(),
            'eviction_policy' => 'lru'
        ];
    }

    public function getAllStats(): array
    {
        return [
            'usage' => $this->getUsageStats(),
            'performance' => $this->getPerformanceStats(),
            'memory' => $this->getMemoryStats(),
            'hit_rate' => $this->getHitRateStats(),
            'miss_rate' => $this->getMissRateStats(),
            'ttl' => $this->getTtlStats(),
            'compression' => $this->getCompressionStats(),
            'encryption' => $this->getEncryptionStats(),
            'tags' => $this->getTagStats(),
            'pattern' => $this->getPatternStats(),
            'strategy' => $this->getStrategyStats()
        ];
    }

    public function resetStats(): void
    {
        $this->stats = [
            'hits' => 0,
            'misses' => 0,
            'puts' => 0,
            'forgets' => 0,
            'flushes' => 0,
            'evictions' => 0
        ];
    }

    public function getDebugInfo(): array
    {
        return [
            'strategy' => 'lru',
            'config' => $this->config,
            'access_order' => $this->accessOrder,
            'stats' => $this->stats,
            'current_size' => $this->getCurrentSize(),
            'max_size' => $this->getMaxSize(),
            'is_full' => $this->isFull()
        ];
    }

    public function getConfigurationInfo(): array
    {
        return [
            'strategy' => 'lru',
            'max_size' => $this->getMaxSize(),
            'ttl' => $this->config['ttl'] ?? 3600,
            'description' => $this->config['description'] ?? 'Least Recently Used'
        ];
    }

    public function getStatusInfo(): array
    {
        return [
            'status' => 'active',
            'healthy' => $this->healthCheck()['healthy'],
            'current_size' => $this->getCurrentSize(),
            'max_size' => $this->getMaxSize(),
            'utilization' => round(($this->getCurrentSize() / $this->getMaxSize()) * 100, 2)
        ];
    }

    public function getVersionInfo(): array
    {
        return [
            'strategy_version' => '1.0.0',
            'implementation' => 'LRUCacheStrategy',
            'compatibility' => 'Laravel 10+'
        ];
    }

    public function getCompatibilityInfo(): array
    {
        return [
            'laravel_version' => '10+',
            'php_version' => '8.1+',
            'cache_drivers' => ['redis', 'memcached', 'database', 'file']
        ];
    }

    public function getSupportInfo(): array
    {
        return [
            'documentation' => 'https://laravel.com/docs/cache',
            'support' => 'https://laravel.com/support',
            'issues' => 'https://github.com/laravel/laravel/issues'
        ];
    }

    /**
     * Aggiorna l'ordine di accesso per una chiave
     */
    private function updateAccessOrder(string $key): void
    {
        // Rimuovi la chiave se esiste già
        $this->removeFromAccessOrder($key);
        
        // Aggiungi la chiave all'inizio (più recente)
        array_unshift($this->accessOrder, $key);
    }

    /**
     * Rimuove una chiave dall'ordine di accesso
     */
    private function removeFromAccessOrder(string $key): void
    {
        $index = array_search($key, $this->accessOrder);
        if ($index !== false) {
            array_splice($this->accessOrder, $index, 1);
        }
    }

    /**
     * Rimuove l'elemento meno recentemente utilizzato
     */
    private function evictLeastRecentlyUsed(): void
    {
        if (empty($this->accessOrder)) {
            return;
        }

        $keyToEvict = array_pop($this->accessOrder);
        Cache::forget($keyToEvict);
        $this->stats['evictions']++;
        
        Log::debug('LRU Cache: Evicted least recently used key', [
            'key' => $keyToEvict
        ]);
    }
}
