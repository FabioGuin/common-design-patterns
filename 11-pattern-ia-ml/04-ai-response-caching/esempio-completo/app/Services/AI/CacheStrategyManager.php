<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\AI\Strategies\CacheStrategyInterface;

class CacheStrategyManager
{
    private array $strategies = [];
    private array $config;

    public function __construct()
    {
        $this->config = config('ai_cache', []);
        $this->initializeStrategies();
    }

    /**
     * Inizializza le strategie di cache
     */
    private function initializeStrategies(): void
    {
        $strategyConfigs = $this->config['strategies'] ?? [];
        
        foreach ($strategyConfigs as $name => $config) {
            try {
                $strategyClass = $config['class'];
                $this->strategies[$name] = new $strategyClass($config);
                
                Log::info('Cache strategy initialized', [
                    'strategy' => $name,
                    'class' => $strategyClass
                ]);
                
            } catch (\Exception $e) {
                Log::error('Failed to initialize cache strategy', [
                    'strategy' => $name,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Salva dati in cache usando una strategia specifica
     */
    public function put(string $key, array $data, int $ttl, string $strategy): bool
    {
        try {
            $strategyInstance = $this->getStrategy($strategy);
            
            if (!$strategyInstance) {
                Log::error('Cache strategy not found', ['strategy' => $strategy]);
                return false;
            }

            $success = $strategyInstance->put($key, $data, $ttl);
            
            if ($success) {
                Log::debug('Data cached successfully', [
                    'key' => $key,
                    'strategy' => $strategy,
                    'ttl' => $ttl
                ]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('Failed to cache data', [
                'key' => $key,
                'strategy' => $strategy,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Recupera dati dalla cache usando una strategia specifica
     */
    public function get(string $key, string $strategy): ?array
    {
        try {
            $strategyInstance = $this->getStrategy($strategy);
            
            if (!$strategyInstance) {
                Log::error('Cache strategy not found', ['strategy' => $strategy]);
                return null;
            }

            $data = $strategyInstance->get($key);
            
            if ($data !== null) {
                Log::debug('Data retrieved from cache', [
                    'key' => $key,
                    'strategy' => $strategy
                ]);
            }

            return $data;

        } catch (\Exception $e) {
            Log::error('Failed to retrieve data from cache', [
                'key' => $key,
                'strategy' => $strategy,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Verifica se una chiave esiste in cache
     */
    public function has(string $key, string $strategy): bool
    {
        try {
            $strategyInstance = $this->getStrategy($strategy);
            
            if (!$strategyInstance) {
                return false;
            }

            return $strategyInstance->has($key);

        } catch (\Exception $e) {
            Log::error('Failed to check cache key existence', [
                'key' => $key,
                'strategy' => $strategy,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Rimuove una chiave dalla cache
     */
    public function forget(string $key, string $strategy): bool
    {
        try {
            $strategyInstance = $this->getStrategy($strategy);
            
            if (!$strategyInstance) {
                return false;
            }

            $success = $strategyInstance->forget($key);
            
            if ($success) {
                Log::debug('Data removed from cache', [
                    'key' => $key,
                    'strategy' => $strategy
                ]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('Failed to remove data from cache', [
                'key' => $key,
                'strategy' => $strategy,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Pulisce tutta la cache per una strategia
     */
    public function flush(string $strategy = null): bool
    {
        try {
            if ($strategy) {
                $strategyInstance = $this->getStrategy($strategy);
                
                if (!$strategyInstance) {
                    return false;
                }

                return $strategyInstance->flush();
            } else {
                // Pulisci tutte le strategie
                $success = true;
                
                foreach ($this->strategies as $name => $strategyInstance) {
                    $result = $strategyInstance->flush();
                    $success = $success && $result;
                }

                return $success;
            }

        } catch (\Exception $e) {
            Log::error('Failed to flush cache', [
                'strategy' => $strategy,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Ottiene le chiavi della cache
     */
    public function getKeys(string $pattern = '*', string $strategy = null, int $limit = 100): array
    {
        try {
            if ($strategy) {
                $strategyInstance = $this->getStrategy($strategy);
                
                if (!$strategyInstance) {
                    return [];
                }

                return $strategyInstance->getKeys($pattern, $limit);
            } else {
                // Ottieni chiavi da tutte le strategie
                $allKeys = [];
                
                foreach ($this->strategies as $name => $strategyInstance) {
                    $keys = $strategyInstance->getKeys($pattern, $limit);
                    $allKeys = array_merge($allKeys, $keys);
                }

                return array_slice($allKeys, 0, $limit);
            }

        } catch (\Exception $e) {
            Log::error('Failed to get cache keys', [
                'pattern' => $pattern,
                'strategy' => $strategy,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Ottiene le statistiche di una strategia
     */
    public function getStats(string $strategy = null): array
    {
        try {
            if ($strategy) {
                $strategyInstance = $this->getStrategy($strategy);
                
                if (!$strategyInstance) {
                    return [];
                }

                return $strategyInstance->getStats();
            } else {
                // Ottieni statistiche da tutte le strategie
                $allStats = [];
                
                foreach ($this->strategies as $name => $strategyInstance) {
                    $allStats[$name] = $strategyInstance->getStats();
                }

                return $allStats;
            }

        } catch (\Exception $e) {
            Log::error('Failed to get cache stats', [
                'strategy' => $strategy,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Ottiene una strategia specifica
     */
    private function getStrategy(string $strategy): ?CacheStrategyInterface
    {
        return $this->strategies[$strategy] ?? null;
    }

    /**
     * Ottiene tutte le strategie disponibili
     */
    public function getAvailableStrategies(): array
    {
        return array_keys($this->strategies);
    }

    /**
     * Ottiene informazioni su una strategia
     */
    public function getStrategyInfo(string $strategy): ?array
    {
        $strategyInstance = $this->getStrategy($strategy);
        
        if (!$strategyInstance) {
            return null;
        }

        $config = $this->config['strategies'][$strategy] ?? [];
        
        return [
            'name' => $strategy,
            'class' => get_class($strategyInstance),
            'config' => $config,
            'stats' => $strategyInstance->getStats()
        ];
    }

    /**
     * Ottiene le informazioni su tutte le strategie
     */
    public function getAllStrategiesInfo(): array
    {
        $strategies = [];
        
        foreach ($this->strategies as $name => $strategyInstance) {
            $strategies[$name] = $this->getStrategyInfo($name);
        }

        return $strategies;
    }

    /**
     * Ottimizza una strategia specifica
     */
    public function optimize(string $strategy): array
    {
        try {
            $strategyInstance = $this->getStrategy($strategy);
            
            if (!$strategyInstance) {
                return ['error' => 'Strategy not found'];
            }

            $result = $strategyInstance->optimize();
            
            Log::info('Cache strategy optimized', [
                'strategy' => $strategy,
                'result' => $result
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Failed to optimize cache strategy', [
                'strategy' => $strategy,
                'error' => $e->getMessage()
            ]);
            
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Ottimizza tutte le strategie
     */
    public function optimizeAll(): array
    {
        $results = [];
        
        foreach ($this->strategies as $name => $strategyInstance) {
            $results[$name] = $this->optimize($name);
        }

        return $results;
    }

    /**
     * Verifica la salute di una strategia
     */
    public function healthCheck(string $strategy = null): array
    {
        try {
            if ($strategy) {
                $strategyInstance = $this->getStrategy($strategy);
                
                if (!$strategyInstance) {
                    return ['healthy' => false, 'error' => 'Strategy not found'];
                }

                return $strategyInstance->healthCheck();
            } else {
                // Verifica tutte le strategie
                $results = [];
                
                foreach ($this->strategies as $name => $strategyInstance) {
                    $results[$name] = $strategyInstance->healthCheck();
                }

                return $results;
            }

        } catch (\Exception $e) {
            Log::error('Cache strategy health check failed', [
                'strategy' => $strategy,
                'error' => $e->getMessage()
            ]);
            
            return ['healthy' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Riavvia una strategia
     */
    public function restart(string $strategy): bool
    {
        try {
            if (!isset($this->strategies[$strategy])) {
                return false;
            }

            // Rimuovi la strategia esistente
            unset($this->strategies[$strategy]);
            
            // Reinizializza la strategia
            $config = $this->config['strategies'][$strategy] ?? [];
            $strategyClass = $config['class'];
            $this->strategies[$strategy] = new $strategyClass($config);
            
            Log::info('Cache strategy restarted', ['strategy' => $strategy]);
            
            return true;

        } catch (\Exception $e) {
            Log::error('Failed to restart cache strategy', [
                'strategy' => $strategy,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}
