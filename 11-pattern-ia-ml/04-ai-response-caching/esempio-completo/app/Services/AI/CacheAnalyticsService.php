<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use App\Models\CacheHit;
use App\Models\AICacheEntry;

class CacheAnalyticsService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('ai_cache', []);
    }

    /**
     * Registra un'operazione di cache
     */
    public function recordOperation(string $type, string $key, float $responseTime): void
    {
        if (!$this->config['analytics']['enabled']) {
            return;
        }

        try {
            CacheHit::create([
                'cache_key' => $key,
                'strategy' => $this->extractStrategyFromKey($key),
                'type' => $type,
                'response_time' => $responseTime,
                'hit_rate' => $this->calculateHitRate($key),
                'metadata' => [
                    'timestamp' => now()->toISOString(),
                    'memory_usage' => memory_get_usage(true)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to record cache operation', [
                'type' => $type,
                'key' => $key,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Registra un hit
     */
    public function recordHit(string $key, float $responseTime): void
    {
        $this->recordOperation('hit', $key, $responseTime);
    }

    /**
     * Registra un miss
     */
    public function recordMiss(string $key, float $responseTime): void
    {
        $this->recordOperation('miss', $key, $responseTime);
    }

    /**
     * Ottiene le analytics della cache
     */
    public function getAnalytics(array $options = []): array
    {
        $period = $options['period'] ?? '7d';
        $strategy = $options['strategy'] ?? null;

        $startDate = $this->getStartDate($period);
        $endDate = now();

        $query = CacheHit::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($strategy) {
            $query->where('strategy', $strategy);
        }

        $hits = $query->get();

        return [
            'period' => $period,
            'strategy' => $strategy,
            'total_operations' => $hits->count(),
            'hits' => $hits->where('type', 'hit')->count(),
            'misses' => $hits->where('type', 'miss')->count(),
            'hit_rate' => $this->calculateHitRateFromHits($hits),
            'miss_rate' => $this->calculateMissRateFromHits($hits),
            'avg_response_time' => round($hits->avg('response_time'), 3),
            'min_response_time' => round($hits->min('response_time'), 3),
            'max_response_time' => round($hits->max('response_time'), 3),
            'total_cache_entries' => AICacheEntry::count(),
            'total_cache_size' => AICacheEntry::sum('size'),
            'compression_rate' => $this->calculateCompressionRate(),
            'cost_savings' => $this->calculateCostSavings($hits)
        ];
    }

    /**
     * Ottiene le metriche di performance
     */
    public function getPerformanceMetrics(): array
    {
        $stats = CacheHit::getAggregateStats();
        $cacheStats = AICacheEntry::getSizeStats();
        $usageStats = AICacheEntry::getUsageStats();

        return [
            'hit_rate' => $stats['hit_rate'],
            'miss_rate' => $stats['miss_rate'],
            'avg_response_time' => $stats['avg_response_time'],
            'total_operations' => $stats['total_operations'],
            'cache_entries' => $cacheStats['total_entries'],
            'cache_size' => $cacheStats['total_size'],
            'avg_entry_size' => $cacheStats['avg_size'],
            'compression_rate' => $cacheStats['compression_rate'],
            'utilization_rate' => $usageStats['utilization_rate'],
            'unused_entries' => $usageStats['unused_entries']
        ];
    }

    /**
     * Ottiene i risparmi di costo
     */
    public function getCostSavings(): array
    {
        $hits = CacheHit::where('type', 'hit')->get();
        $apiCostPerRequest = $this->config['cost_optimization']['api_cost_per_request'] ?? 0.01;
        $cacheCostPerMB = $this->config['cost_optimization']['cache_cost_per_mb'] ?? 0.001;

        $callsAvoided = $hits->count();
        $apiCostSaved = $callsAvoided * $apiCostPerRequest;
        
        $cacheSizeMB = AICacheEntry::sum('size') / (1024 * 1024);
        $cacheCost = $cacheSizeMB * $cacheCostPerMB;
        
        $netSavings = $apiCostSaved - $cacheCost;

        return [
            'calls_avoided' => $callsAvoided,
            'api_cost_saved' => round($apiCostSaved, 4),
            'cache_cost' => round($cacheCost, 4),
            'net_savings' => round($netSavings, 4),
            'savings_percentage' => $apiCostSaved > 0 ? round(($netSavings / $apiCostSaved) * 100, 2) : 0,
            'roi' => $cacheCost > 0 ? round(($netSavings / $cacheCost) * 100, 2) : 0
        ];
    }

    /**
     * Ottiene il hit rate
     */
    public function getHitRate(): float
    {
        $stats = CacheHit::getAggregateStats();
        return $stats['hit_rate'];
    }

    /**
     * Ottiene le statistiche per strategia
     */
    public function getStatsByStrategy(): array
    {
        return CacheHit::getStatsByStrategy();
    }

    /**
     * Ottiene le statistiche giornaliere
     */
    public function getDailyStats(int $days = 7): array
    {
        return CacheHit::getDailyStats($days);
    }

    /**
     * Ottiene le statistiche orarie
     */
    public function getHourlyStats(int $hours = 24): array
    {
        return CacheHit::getHourlyStats($hours);
    }

    /**
     * Ottiene le statistiche di performance
     */
    public function getPerformanceStats(): array
    {
        return CacheHit::getPerformanceStats();
    }

    /**
     * Ottiene le statistiche di hit rate
     */
    public function getHitRateStats(): array
    {
        return CacheHit::getHitRateStats();
    }

    /**
     * Ottiene le statistiche per tempo di risposta
     */
    public function getResponseTimeStats(): array
    {
        return CacheHit::getResponseTimeStats();
    }

    /**
     * Ottiene le statistiche in tempo reale
     */
    public function getRealTimeStats(): array
    {
        return CacheHit::getRealTimeStats();
    }

    /**
     * Ottiene le statistiche di tendenza
     */
    public function getTrendStats(): array
    {
        return CacheHit::getTrendStats();
    }

    /**
     * Ottiene le statistiche per tag
     */
    public function getStatsByTag(): array
    {
        return AICacheEntry::getStatsByTag();
    }

    /**
     * Ottiene le chiavi problematiche
     */
    public function getProblematicKeys(): array
    {
        return AICacheEntry::getProblematicKeys();
    }

    /**
     * Ottiene le raccomandazioni per l'ottimizzazione
     */
    public function getOptimizationRecommendations(): array
    {
        $recommendations = [];
        
        $hitRate = $this->getHitRate();
        if ($hitRate < 70) {
            $recommendations[] = [
                'type' => 'hit_rate',
                'priority' => 'high',
                'message' => "Hit rate basso ({$hitRate}%). Considera di aumentare il TTL o migliorare le chiavi di cache.",
                'action' => 'increase_ttl'
            ];
        }

        $unusedEntries = AICacheEntry::where('hit_count', 0)->count();
        $totalEntries = AICacheEntry::count();
        if ($unusedEntries > $totalEntries * 0.3) {
            $recommendations[] = [
                'type' => 'unused_entries',
                'priority' => 'medium',
                'message' => "Molte chiavi inutilizzate ({$unusedEntries}/{$totalEntries}). Considera di pulire la cache.",
                'action' => 'cleanup_unused'
            ];
        }

        $largeEntries = AICacheEntry::where('size', '>', 1024 * 1024)->count();
        if ($largeEntries > 0) {
            $recommendations[] = [
                'type' => 'large_entries',
                'priority' => 'low',
                'message' => "Trovate {$largeEntries} chiavi grandi (>1MB). Considera la compressione.",
                'action' => 'enable_compression'
            ];
        }

        $compressionRate = $this->calculateCompressionRate();
        if ($compressionRate < 50) {
            $recommendations[] = [
                'type' => 'compression',
                'priority' => 'medium',
                'message' => "Tasso di compressione basso ({$compressionRate}%). Abilita la compressione per risparmiare spazio.",
                'action' => 'enable_compression'
            ];
        }

        return $recommendations;
    }

    /**
     * Ottiene le metriche di salute della cache
     */
    public function getHealthMetrics(): array
    {
        $hitRate = $this->getHitRate();
        $unusedEntries = AICacheEntry::where('hit_count', 0)->count();
        $totalEntries = AICacheEntry::count();
        $utilizationRate = $totalEntries > 0 ? ($totalEntries - $unusedEntries) / $totalEntries * 100 : 0;

        $healthScore = 0;
        $healthIssues = [];

        // Hit rate score (40% del punteggio)
        if ($hitRate >= 80) {
            $healthScore += 40;
        } elseif ($hitRate >= 60) {
            $healthScore += 30;
        } elseif ($hitRate >= 40) {
            $healthScore += 20;
        } else {
            $healthScore += 10;
            $healthIssues[] = 'Hit rate molto basso';
        }

        // Utilization score (30% del punteggio)
        if ($utilizationRate >= 80) {
            $healthScore += 30;
        } elseif ($utilizationRate >= 60) {
            $healthScore += 25;
        } elseif ($utilizationRate >= 40) {
            $healthScore += 20;
        } else {
            $healthScore += 10;
            $healthIssues[] = 'Utilizzo della cache basso';
        }

        // Performance score (30% del punteggio)
        $avgResponseTime = CacheHit::avg('response_time') ?? 0;
        if ($avgResponseTime <= 0.01) {
            $healthScore += 30;
        } elseif ($avgResponseTime <= 0.05) {
            $healthScore += 25;
        } elseif ($avgResponseTime <= 0.1) {
            $healthScore += 20;
        } else {
            $healthScore += 10;
            $healthIssues[] = 'Tempo di risposta lento';
        }

        $healthStatus = 'excellent';
        if ($healthScore < 60) {
            $healthStatus = 'poor';
        } elseif ($healthScore < 80) {
            $healthStatus = 'fair';
        } elseif ($healthScore < 90) {
            $healthStatus = 'good';
        }

        return [
            'health_score' => $healthScore,
            'health_status' => $healthStatus,
            'health_issues' => $healthIssues,
            'hit_rate' => $hitRate,
            'utilization_rate' => round($utilizationRate, 2),
            'avg_response_time' => round($avgResponseTime, 3),
            'total_entries' => $totalEntries,
            'unused_entries' => $unusedEntries
        ];
    }

    /**
     * Calcola il hit rate da una collezione di hit
     */
    private function calculateHitRateFromHits($hits): float
    {
        $total = $hits->count();
        if ($total === 0) {
            return 0;
        }

        $hitsCount = $hits->where('type', 'hit')->count();
        return round(($hitsCount / $total) * 100, 2);
    }

    /**
     * Calcola il miss rate da una collezione di hit
     */
    private function calculateMissRateFromHits($hits): float
    {
        $total = $hits->count();
        if ($total === 0) {
            return 0;
        }

        $missesCount = $hits->where('type', 'miss')->count();
        return round(($missesCount / $total) * 100, 2);
    }

    /**
     * Calcola il tasso di compressione
     */
    private function calculateCompressionRate(): float
    {
        $totalEntries = AICacheEntry::count();
        if ($totalEntries === 0) {
            return 0;
        }

        $compressedEntries = AICacheEntry::where('compressed', true)->count();
        return round(($compressedEntries / $totalEntries) * 100, 2);
    }

    /**
     * Calcola i risparmi di costo
     */
    private function calculateCostSavings($hits): array
    {
        $apiCostPerRequest = $this->config['cost_optimization']['api_cost_per_request'] ?? 0.01;
        $cacheCostPerMB = $this->config['cost_optimization']['cache_cost_per_mb'] ?? 0.001;

        $callsAvoided = $hits->where('type', 'hit')->count();
        $apiCostSaved = $callsAvoided * $apiCostPerRequest;
        
        $cacheSizeMB = AICacheEntry::sum('size') / (1024 * 1024);
        $cacheCost = $cacheSizeMB * $cacheCostPerMB;
        
        $netSavings = $apiCostSaved - $cacheCost;

        return [
            'calls_avoided' => $callsAvoided,
            'api_cost_saved' => round($apiCostSaved, 4),
            'cache_cost' => round($cacheCost, 4),
            'net_savings' => round($netSavings, 4)
        ];
    }

    /**
     * Calcola il hit rate per una chiave specifica
     */
    private function calculateHitRate(string $key): float
    {
        $total = CacheHit::where('cache_key', $key)->count();
        if ($total === 0) {
            return 0;
        }

        $hits = CacheHit::where('cache_key', $key)->where('type', 'hit')->count();
        return round(($hits / $total) * 100, 2);
    }

    /**
     * Estrae la strategia dalla chiave di cache
     */
    private function extractStrategyFromKey(string $key): string
    {
        $parts = explode(':', $key);
        return $parts[1] ?? 'unknown';
    }

    /**
     * Ottiene la data di inizio per un periodo
     */
    private function getStartDate(string $period): \Carbon\Carbon
    {
        switch ($period) {
            case '1h':
                return now()->subHour();
            case '24h':
                return now()->subDay();
            case '7d':
                return now()->subDays(7);
            case '30d':
                return now()->subDays(30);
            case '90d':
                return now()->subDays(90);
            default:
                return now()->subDays(7);
        }
    }
}
