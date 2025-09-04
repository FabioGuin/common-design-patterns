<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\AICacheEntry;
use App\Services\AI\CacheStrategyManager;

class CacheInvalidationService
{
    private CacheStrategyManager $strategyManager;
    private array $config;

    public function __construct(CacheStrategyManager $strategyManager)
    {
        $this->strategyManager = $strategyManager;
        $this->config = config('ai_cache', []);
    }

    /**
     * Invalida cache per pattern
     */
    public function invalidateByPattern(string $pattern, array $options = []): int
    {
        try {
            $strategy = $options['strategy'] ?? null;
            $count = 0;

            if ($strategy) {
                // Invalida per una strategia specifica
                $count = $this->invalidatePatternForStrategy($pattern, $strategy);
            } else {
                // Invalida per tutte le strategie
                $strategies = $this->strategyManager->getAvailableStrategies();
                
                foreach ($strategies as $strategyName) {
                    $count += $this->invalidatePatternForStrategy($pattern, $strategyName);
                }
            }

            Log::info('Cache invalidated by pattern', [
                'pattern' => $pattern,
                'strategy' => $strategy,
                'count' => $count
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error('Failed to invalidate cache by pattern', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }

    /**
     * Invalida cache per tag
     */
    public function invalidateByTag(string $tag, array $options = []): int
    {
        try {
            $strategy = $options['strategy'] ?? null;
            $count = 0;

            // Trova le entry che contengono il tag
            $query = AICacheEntry::whereJsonContains('tags', $tag);
            
            if ($strategy) {
                $query->where('strategy', $strategy);
            }

            $entries = $query->get();

            foreach ($entries as $entry) {
                $success = $this->strategyManager->forget($entry->cache_key, $entry->strategy);
                
                if ($success) {
                    $entry->delete();
                    $count++;
                }
            }

            Log::info('Cache invalidated by tag', [
                'tag' => $tag,
                'strategy' => $strategy,
                'count' => $count
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error('Failed to invalidate cache by tag', [
                'tag' => $tag,
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }

    /**
     * Invalida cache per chiave specifica
     */
    public function invalidateByKey(string $key, array $options = []): bool
    {
        try {
            $strategy = $options['strategy'] ?? $this->config['default_strategy'];
            $cacheKey = $this->generateCacheKey($key, $strategy);

            $success = $this->strategyManager->forget($cacheKey, $strategy);
            
            if ($success) {
                AICacheEntry::where('cache_key', $cacheKey)->delete();
            }

            Log::info('Cache invalidated by key', [
                'key' => $key,
                'strategy' => $strategy,
                'success' => $success
            ]);

            return $success;

        } catch (\Exception $e) {
            Log::error('Failed to invalidate cache by key', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Invalida cache per regole di invalidazione
     */
    public function invalidateByRules(array $rules): array
    {
        $results = [];

        foreach ($rules as $rule) {
            $pattern = $rule['pattern'] ?? null;
            $tag = $rule['tag'] ?? null;
            $strategy = $rule['strategy'] ?? null;

            if ($pattern) {
                $count = $this->invalidateByPattern($pattern, ['strategy' => $strategy]);
                $results[] = [
                    'type' => 'pattern',
                    'pattern' => $pattern,
                    'strategy' => $strategy,
                    'count' => $count
                ];
            }

            if ($tag) {
                $count = $this->invalidateByTag($tag, ['strategy' => $strategy]);
                $results[] = [
                    'type' => 'tag',
                    'tag' => $tag,
                    'strategy' => $strategy,
                    'count' => $count
                ];
            }
        }

        return $results;
    }

    /**
     * Invalida cache per trigger
     */
    public function invalidateByTrigger(string $trigger): array
    {
        try {
            $invalidationRules = $this->config['invalidation_rules'] ?? [];
            $results = [];

            foreach ($invalidationRules as $pattern => $rule) {
                if (($rule['trigger'] ?? '') === $trigger) {
                    $count = $this->invalidateByPattern($pattern);
                    $results[] = [
                        'pattern' => $pattern,
                        'count' => $count,
                        'ttl' => $rule['ttl'] ?? 0
                    ];
                }
            }

            Log::info('Cache invalidated by trigger', [
                'trigger' => $trigger,
                'results' => $results
            ]);

            return $results;

        } catch (\Exception $e) {
            Log::error('Failed to invalidate cache by trigger', [
                'trigger' => $trigger,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Invalida cache per TTL
     */
    public function invalidateExpired(): int
    {
        try {
            $expiredEntries = AICacheEntry::expired()->get();
            $count = 0;

            foreach ($expiredEntries as $entry) {
                $success = $this->strategyManager->forget($entry->cache_key, $entry->strategy);
                
                if ($success) {
                    $entry->delete();
                    $count++;
                }
            }

            Log::info('Expired cache entries invalidated', [
                'count' => $count
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error('Failed to invalidate expired cache entries', [
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }

    /**
     * Invalida cache per dimensione
     */
    public function invalidateBySize(int $maxSize): int
    {
        try {
            $largeEntries = AICacheEntry::where('size', '>', $maxSize)->get();
            $count = 0;

            foreach ($largeEntries as $entry) {
                $success = $this->strategyManager->forget($entry->cache_key, $entry->strategy);
                
                if ($success) {
                    $entry->delete();
                    $count++;
                }
            }

            Log::info('Large cache entries invalidated', [
                'max_size' => $maxSize,
                'count' => $count
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error('Failed to invalidate large cache entries', [
                'max_size' => $maxSize,
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }

    /**
     * Invalida cache per hit count
     */
    public function invalidateUnused(int $maxHits = 0): int
    {
        try {
            $unusedEntries = AICacheEntry::where('hit_count', '<=', $maxHits)->get();
            $count = 0;

            foreach ($unusedEntries as $entry) {
                $success = $this->strategyManager->forget($entry->cache_key, $entry->strategy);
                
                if ($success) {
                    $entry->delete();
                    $count++;
                }
            }

            Log::info('Unused cache entries invalidated', [
                'max_hits' => $maxHits,
                'count' => $count
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error('Failed to invalidate unused cache entries', [
                'max_hits' => $maxHits,
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }

    /**
     * Invalida cache per data di creazione
     */
    public function invalidateByDate(\DateTime $beforeDate): int
    {
        try {
            $oldEntries = AICacheEntry::where('created_at', '<', $beforeDate)->get();
            $count = 0;

            foreach ($oldEntries as $entry) {
                $success = $this->strategyManager->forget($entry->cache_key, $entry->strategy);
                
                if ($success) {
                    $entry->delete();
                    $count++;
                }
            }

            Log::info('Old cache entries invalidated', [
                'before_date' => $beforeDate->format('Y-m-d H:i:s'),
                'count' => $count
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error('Failed to invalidate old cache entries', [
                'before_date' => $beforeDate->format('Y-m-d H:i:s'),
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }

    /**
     * Invalida cache per strategia
     */
    public function invalidateByStrategy(string $strategy): int
    {
        try {
            $entries = AICacheEntry::where('strategy', $strategy)->get();
            $count = 0;

            foreach ($entries as $entry) {
                $success = $this->strategyManager->forget($entry->cache_key, $entry->strategy);
                
                if ($success) {
                    $entry->delete();
                    $count++;
                }
            }

            Log::info('Cache invalidated by strategy', [
                'strategy' => $strategy,
                'count' => $count
            ]);

            return $count;

        } catch (\Exception $e) {
            Log::error('Failed to invalidate cache by strategy', [
                'strategy' => $strategy,
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }

    /**
     * Invalida cache per pattern specifico in una strategia
     */
    private function invalidatePatternForStrategy(string $pattern, string $strategy): int
    {
        $count = 0;
        
        // Trova le entry che corrispondono al pattern
        $entries = AICacheEntry::where('strategy', $strategy)
            ->where('original_key', 'like', str_replace('*', '%', $pattern))
            ->get();

        foreach ($entries as $entry) {
            $success = $this->strategyManager->forget($entry->cache_key, $entry->strategy);
            
            if ($success) {
                $entry->delete();
                $count++;
            }
        }

        return $count;
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
     * Ottiene le regole di invalidazione
     */
    public function getInvalidationRules(): array
    {
        return $this->config['invalidation_rules'] ?? [];
    }

    /**
     * Aggiunge una regola di invalidazione
     */
    public function addInvalidationRule(string $pattern, array $rule): void
    {
        $this->config['invalidation_rules'][$pattern] = $rule;
        
        Log::info('Invalidation rule added', [
            'pattern' => $pattern,
            'rule' => $rule
        ]);
    }

    /**
     * Rimuove una regola di invalidazione
     */
    public function removeInvalidationRule(string $pattern): bool
    {
        if (isset($this->config['invalidation_rules'][$pattern])) {
            unset($this->config['invalidation_rules'][$pattern]);
            
            Log::info('Invalidation rule removed', [
                'pattern' => $pattern
            ]);
            
            return true;
        }

        return false;
    }

    /**
     * Ottiene le statistiche di invalidazione
     */
    public function getInvalidationStats(): array
    {
        $totalEntries = AICacheEntry::count();
        $expiredEntries = AICacheEntry::expired()->count();
        $unusedEntries = AICacheEntry::where('hit_count', 0)->count();
        $largeEntries = AICacheEntry::where('size', '>', 1024 * 1024)->count();

        return [
            'total_entries' => $totalEntries,
            'expired_entries' => $expiredEntries,
            'unused_entries' => $unusedEntries,
            'large_entries' => $largeEntries,
            'expired_percentage' => $totalEntries > 0 ? round(($expiredEntries / $totalEntries) * 100, 2) : 0,
            'unused_percentage' => $totalEntries > 0 ? round(($unusedEntries / $totalEntries) * 100, 2) : 0,
            'large_percentage' => $totalEntries > 0 ? round(($largeEntries / $totalEntries) * 100, 2) : 0
        ];
    }

    /**
     * Esegue pulizia automatica
     */
    public function autoCleanup(): array
    {
        $results = [
            'expired_removed' => 0,
            'unused_removed' => 0,
            'large_removed' => 0,
            'total_removed' => 0
        ];

        try {
            // Rimuovi entry scadute
            $results['expired_removed'] = $this->invalidateExpired();
            
            // Rimuovi entry inutilizzate
            $results['unused_removed'] = $this->invalidateUnused(0);
            
            // Rimuovi entry troppo grandi
            $maxSize = $this->config['max_cache_size'] * 1024; // Converti in bytes
            $results['large_removed'] = $this->invalidateBySize($maxSize);
            
            $results['total_removed'] = $results['expired_removed'] + 
                                      $results['unused_removed'] + 
                                      $results['large_removed'];

            Log::info('Auto cleanup completed', $results);

        } catch (\Exception $e) {
            Log::error('Auto cleanup failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }
}
