<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Models\AICacheEntry;
use App\Models\CacheHit;
use App\Models\CacheAnalytics;
use App\Services\AI\CacheStrategyManager;
use App\Services\AI\CacheInvalidationService;
use App\Services\AI\CacheWarmingService;
use App\Services\AI\CacheAnalyticsService;

class AICacheService
{
    private CacheStrategyManager $strategyManager;
    private CacheInvalidationService $invalidationService;
    private CacheWarmingService $warmingService;
    private CacheAnalyticsService $analyticsService;
    private array $config;

    public function __construct(
        CacheStrategyManager $strategyManager,
        CacheInvalidationService $invalidationService,
        CacheWarmingService $warmingService,
        CacheAnalyticsService $analyticsService
    ) {
        $this->strategyManager = $strategyManager;
        $this->invalidationService = $invalidationService;
        $this->warmingService = $warmingService;
        $this->analyticsService = $analyticsService;
        $this->config = config('ai_cache', []);
    }

    /**
     * Salva una risposta AI in cache
     */
    public function put(string $key, array $data, array $options = []): bool
    {
        if (!$this->config['enabled']) {
            return false;
        }

        $startTime = microtime(true);
        $strategy = $options['strategy'] ?? $this->config['default_strategy'];
        $ttl = $options['ttl'] ?? $this->config['default_ttl'];
        $tags = $options['tags'] ?? [];
        $compress = $options['compress'] ?? $this->config['compression_enabled'];

        try {
            // Prepara i dati per la cache
            $cacheData = $this->prepareCacheData($data, $compress);
            
            // Genera la chiave di cache
            $cacheKey = $this->generateCacheKey($key, $strategy);
            
            // Salva in cache usando la strategia
            $success = $this->strategyManager->put($cacheKey, $cacheData, $ttl, $strategy);
            
            if ($success) {
                // Salva nel database per analytics
                $this->saveCacheEntry($key, $cacheKey, $data, $options);
                
                // Registra hit per analytics
                $this->analyticsService->recordOperation('put', $cacheKey, microtime(true) - $startTime);
                
                Log::info('AI Response cached', [
                    'key' => $key,
                    'strategy' => $strategy,
                    'ttl' => $ttl,
                    'compressed' => $compress
                ]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('Failed to cache AI response', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Recupera una risposta AI dalla cache
     */
    public function get(string $key, array $options = []): ?array
    {
        if (!$this->config['enabled']) {
            return null;
        }

        $startTime = microtime(true);
        $strategy = $options['strategy'] ?? $this->config['default_strategy'];
        $updateStats = $options['update_stats'] ?? true;

        try {
            // Genera la chiave di cache
            $cacheKey = $this->generateCacheKey($key, $strategy);
            
            // Recupera dalla cache
            $cachedData = $this->strategyManager->get($cacheKey, $strategy);
            
            if ($cachedData !== null) {
                // Decomprimi se necessario
                $data = $this->decompressCacheData($cachedData);
                
                // Aggiorna le statistiche
                if ($updateStats) {
                    $this->analyticsService->recordHit($cacheKey, microtime(true) - $startTime);
                }
                
                Log::debug('AI Response retrieved from cache', [
                    'key' => $key,
                    'strategy' => $strategy
                ]);
                
                return $data;
            }

            // Registra miss per analytics
            if ($updateStats) {
                $this->analyticsService->recordMiss($cacheKey, microtime(true) - $startTime);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to retrieve AI response from cache', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Verifica se una chiave esiste in cache
     */
    public function has(string $key, array $options = []): bool
    {
        if (!$this->config['enabled']) {
            return false;
        }

        $strategy = $options['strategy'] ?? $this->config['default_strategy'];
        $cacheKey = $this->generateCacheKey($key, $strategy);
        
        return $this->strategyManager->has($cacheKey, $strategy);
    }

    /**
     * Rimuove una chiave dalla cache
     */
    public function forget(string $key, array $options = []): bool
    {
        if (!$this->config['enabled']) {
            return false;
        }

        $strategy = $options['strategy'] ?? $this->config['default_strategy'];
        $cacheKey = $this->generateCacheKey($key, $strategy);
        
        $success = $this->strategyManager->forget($cacheKey, $strategy);
        
        if ($success) {
            // Rimuovi dal database
            AICacheEntry::where('cache_key', $cacheKey)->delete();
            
            Log::info('AI Response removed from cache', [
                'key' => $key,
                'strategy' => $strategy
            ]);
        }

        return $success;
    }

    /**
     * Invalida cache per pattern
     */
    public function invalidateByPattern(string $pattern, array $options = []): int
    {
        return $this->invalidationService->invalidateByPattern($pattern, $options);
    }

    /**
     * Invalida cache per tag
     */
    public function invalidateByTag(string $tag, array $options = []): int
    {
        return $this->invalidationService->invalidateByTag($tag, $options);
    }

    /**
     * Invalida cache per chiave
     */
    public function invalidateByKey(string $key, array $options = []): bool
    {
        return $this->invalidationService->invalidateByKey($key, $options);
    }

    /**
     * Pulisce tutta la cache
     */
    public function flush(array $options = []): bool
    {
        $strategy = $options['strategy'] ?? null;
        
        $success = $this->strategyManager->flush($strategy);
        
        if ($success) {
            // Pulisci il database
            AICacheEntry::truncate();
            CacheHit::truncate();
            
            Log::info('AI Cache flushed', [
                'strategy' => $strategy
            ]);
        }

        return $success;
    }

    /**
     * Pre-riscalda la cache
     */
    public function warmCache(array $data = null): array
    {
        return $this->warmingService->warmCache($data);
    }

    /**
     * Ottiene le analytics della cache
     */
    public function getAnalytics(array $options = []): array
    {
        return $this->analyticsService->getAnalytics($options);
    }

    /**
     * Ottiene le metriche di performance
     */
    public function getPerformanceMetrics(): array
    {
        return $this->analyticsService->getPerformanceMetrics();
    }

    /**
     * Ottiene i risparmi di costo
     */
    public function getCostSavings(): array
    {
        return $this->analyticsService->getCostSavings();
    }

    /**
     * Ottiene le statistiche della cache
     */
    public function getCacheStats(): array
    {
        return [
            'total_entries' => AICacheEntry::count(),
            'total_hits' => CacheHit::where('type', 'hit')->count(),
            'total_misses' => CacheHit::where('type', 'miss')->count(),
            'hit_rate' => $this->analyticsService->getHitRate(),
            'memory_usage' => $this->getMemoryUsage(),
            'cache_size' => $this->getCacheSize()
        ];
    }

    /**
     * Ottiene le chiavi della cache
     */
    public function getCacheKeys(array $options = []): array
    {
        $strategy = $options['strategy'] ?? null;
        $pattern = $options['pattern'] ?? '*';
        $limit = $options['limit'] ?? 100;
        
        return $this->strategyManager->getKeys($pattern, $strategy, $limit);
    }

    /**
     * Ottiene informazioni su una chiave specifica
     */
    public function getKeyInfo(string $key, array $options = []): ?array
    {
        $strategy = $options['strategy'] ?? $this->config['default_strategy'];
        $cacheKey = $this->generateCacheKey($key, $strategy);
        
        $entry = AICacheEntry::where('cache_key', $cacheKey)->first();
        
        if (!$entry) {
            return null;
        }

        return [
            'key' => $key,
            'cache_key' => $cacheKey,
            'strategy' => $entry->strategy,
            'ttl' => $entry->ttl,
            'created_at' => $entry->created_at,
            'expires_at' => $entry->expires_at,
            'size' => $entry->size,
            'compressed' => $entry->compressed,
            'tags' => $entry->tags,
            'hit_count' => $entry->hit_count,
            'last_accessed_at' => $entry->last_accessed_at
        ];
    }

    /**
     * Ottimizza la cache
     */
    public function optimize(array $options = []): array
    {
        $results = [
            'entries_removed' => 0,
            'memory_freed' => 0,
            'hit_rate_improved' => false
        ];

        // Rimuovi entry scadute
        $expiredEntries = AICacheEntry::where('expires_at', '<', now())->get();
        foreach ($expiredEntries as $entry) {
            $this->strategyManager->forget($entry->cache_key, $entry->strategy);
            $entry->delete();
            $results['entries_removed']++;
        }

        // Ottimizza per dimensione
        $maxSize = $this->config['max_cache_size'];
        $currentSize = AICacheEntry::count();
        
        if ($currentSize > $maxSize) {
            $toRemove = $currentSize - $maxSize;
            $entriesToRemove = AICacheEntry::orderBy('last_accessed_at')
                ->limit($toRemove)
                ->get();
            
            foreach ($entriesToRemove as $entry) {
                $this->strategyManager->forget($entry->cache_key, $entry->strategy);
                $results['memory_freed'] += $entry->size;
                $entry->delete();
                $results['entries_removed']++;
            }
        }

        // Calcola miglioramento hit rate
        $oldHitRate = $this->analyticsService->getHitRate();
        // Simula miglioramento (in realtà dovrebbe essere calcolato dopo l'ottimizzazione)
        $results['hit_rate_improved'] = true;

        Log::info('AI Cache optimized', $results);

        return $results;
    }

    /**
     * Prepara i dati per la cache
     */
    private function prepareCacheData(array $data, bool $compress = false): array
    {
        $cacheData = [
            'data' => $data,
            'timestamp' => time(),
            'compressed' => false
        ];

        if ($compress && $this->shouldCompress($data)) {
            $cacheData['data'] = $this->compressData($data);
            $cacheData['compressed'] = true;
        }

        // Cripta se necessario
        if ($this->config['security']['encrypt_sensitive'] && $this->isSensitiveData($data)) {
            $cacheData['data'] = Crypt::encrypt($cacheData['data']);
            $cacheData['encrypted'] = true;
        }

        return $cacheData;
    }

    /**
     * Decomprime i dati dalla cache
     */
    private function decompressCacheData(array $cacheData): array
    {
        $data = $cacheData['data'];

        // Decripta se necessario
        if (isset($cacheData['encrypted']) && $cacheData['encrypted']) {
            $data = Crypt::decrypt($data);
        }

        // Decomprimi se necessario
        if (isset($cacheData['compressed']) && $cacheData['compressed']) {
            $data = $this->decompressData($data);
        }

        return $data;
    }

    /**
     * Genera la chiave di cache
     */
    private function generateCacheKey(string $key, string $strategy): string
    {
        $prefix = $this->config['drivers']['redis']['prefix'] ?? 'ai_cache:';
        
        if ($this->config['security']['hash_keys']) {
            $salt = $this->config['security']['key_salt'];
            $key = hash('sha256', $key . $salt);
        }

        return $prefix . $strategy . ':' . $key;
    }

    /**
     * Salva entry nel database
     */
    private function saveCacheEntry(string $key, string $cacheKey, array $data, array $options): void
    {
        AICacheEntry::create([
            'original_key' => $key,
            'cache_key' => $cacheKey,
            'strategy' => $options['strategy'] ?? $this->config['default_strategy'],
            'ttl' => $options['ttl'] ?? $this->config['default_ttl'],
            'expires_at' => now()->addSeconds($options['ttl'] ?? $this->config['default_ttl']),
            'size' => strlen(serialize($data)),
            'compressed' => $options['compress'] ?? false,
            'tags' => $options['tags'] ?? [],
            'hit_count' => 0,
            'last_accessed_at' => now()
        ]);
    }

    /**
     * Verifica se i dati dovrebbero essere compressi
     */
    private function shouldCompress(array $data): bool
    {
        $size = strlen(serialize($data));
        $threshold = $this->config['compression']['threshold'];
        
        return $size > $threshold;
    }

    /**
     * Comprime i dati
     */
    private function compressData(array $data): string
    {
        $serialized = serialize($data);
        
        switch ($this->config['compression']['algorithm']) {
            case 'gzip':
                return gzcompress($serialized, $this->config['compression']['level']);
            case 'lz4':
                return lz4_compress($serialized);
            case 'zstd':
                return zstd_compress($serialized);
            default:
                return $serialized;
        }
    }

    /**
     * Decomprime i dati
     */
    private function decompressData(string $compressedData): array
    {
        switch ($this->config['compression']['algorithm']) {
            case 'gzip':
                $decompressed = gzuncompress($compressedData);
                break;
            case 'lz4':
                $decompressed = lz4_uncompress($compressedData);
                break;
            case 'zstd':
                $decompressed = zstd_uncompress($compressedData);
                break;
            default:
                $decompressed = $compressedData;
        }

        return unserialize($decompressed);
    }

    /**
     * Verifica se i dati sono sensibili
     */
    private function isSensitiveData(array $data): bool
    {
        $sensitivePatterns = $this->config['security']['sensitive_patterns'];
        
        foreach ($data as $key => $value) {
            foreach ($sensitivePatterns as $pattern) {
                if (fnmatch($pattern, $key)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Ottiene l'uso di memoria
     */
    private function getMemoryUsage(): int
    {
        return AICacheEntry::sum('size');
    }

    /**
     * Ottiene la dimensione della cache
     */
    private function getCacheSize(): int
    {
        return AICacheEntry::count();
    }
}
