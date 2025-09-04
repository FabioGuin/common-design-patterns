<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CacheHit extends Model
{
    use HasFactory;

    protected $fillable = [
        'cache_key',
        'strategy',
        'type',
        'response_time',
        'hit_rate',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'response_time' => 'float'
    ];

    /**
     * Scope per hit
     */
    public function scopeHits($query)
    {
        return $query->where('type', 'hit');
    }

    /**
     * Scope per miss
     */
    public function scopeMisses($query)
    {
        return $query->where('type', 'miss');
    }

    /**
     * Scope per strategia specifica
     */
    public function scopeByStrategy($query, string $strategy)
    {
        return $query->where('strategy', $strategy);
    }

    /**
     * Scope per periodo
     */
    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope per tempo di risposta
     */
    public function scopeByResponseTime($query, float $minTime, float $maxTime = null)
    {
        if ($maxTime) {
            return $query->whereBetween('response_time', [$minTime, $maxTime]);
        }
        return $query->where('response_time', '>=', $minTime);
    }

    /**
     * Scope per hit rate
     */
    public function scopeByHitRate($query, float $minRate, float $maxRate = null)
    {
        if ($maxRate) {
            return $query->whereBetween('hit_rate', [$minRate, $maxRate]);
        }
        return $query->where('hit_rate', '>=', $minRate);
    }

    /**
     * Ottiene le statistiche aggregate
     */
    public static function getAggregateStats(): array
    {
        $stats = static::selectRaw('
            COUNT(*) as total_operations,
            COUNT(CASE WHEN type = "hit" THEN 1 END) as total_hits,
            COUNT(CASE WHEN type = "miss" THEN 1 END) as total_misses,
            AVG(response_time) as avg_response_time,
            MIN(response_time) as min_response_time,
            MAX(response_time) as max_response_time,
            AVG(hit_rate) as avg_hit_rate
        ')->first();

        return [
            'total_operations' => $stats->total_operations,
            'total_hits' => $stats->total_hits,
            'total_misses' => $stats->total_misses,
            'hit_rate' => $stats->total_operations > 0 ? round(($stats->total_hits / $stats->total_operations) * 100, 2) : 0,
            'miss_rate' => $stats->total_operations > 0 ? round(($stats->total_misses / $stats->total_operations) * 100, 2) : 0,
            'avg_response_time' => round($stats->avg_response_time, 3),
            'min_response_time' => round($stats->min_response_time, 3),
            'max_response_time' => round($stats->max_response_time, 3),
            'avg_hit_rate' => round($stats->avg_hit_rate, 2)
        ];
    }

    /**
     * Ottiene le statistiche per strategia
     */
    public static function getStatsByStrategy(): array
    {
        return static::select('strategy')
            ->selectRaw('COUNT(*) as total_operations')
            ->selectRaw('COUNT(CASE WHEN type = "hit" THEN 1 END) as hits')
            ->selectRaw('COUNT(CASE WHEN type = "miss" THEN 1 END) as misses')
            ->selectRaw('AVG(response_time) as avg_response_time')
            ->selectRaw('AVG(hit_rate) as avg_hit_rate')
            ->groupBy('strategy')
            ->get()
            ->map(function($stat) {
                $total = $stat->total_operations;
                return [
                    'strategy' => $stat->strategy,
                    'total_operations' => $total,
                    'hits' => $stat->hits,
                    'misses' => $stat->misses,
                    'hit_rate' => $total > 0 ? round(($stat->hits / $total) * 100, 2) : 0,
                    'miss_rate' => $total > 0 ? round(($stat->misses / $total) * 100, 2) : 0,
                    'avg_response_time' => round($stat->avg_response_time, 3),
                    'avg_hit_rate' => round($stat->avg_hit_rate, 2)
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene le statistiche giornaliere
     */
    public static function getDailyStats(int $days = 7): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $hits = static::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function($record) {
                return $record->created_at->format('Y-m-d');
            });

        $dailyStats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayHits = $hits->get($date, collect());

            $totalOps = $dayHits->count();
            $hitsCount = $dayHits->where('type', 'hit')->count();
            $missesCount = $dayHits->where('type', 'miss')->count();

            $dailyStats[$date] = [
                'date' => $date,
                'total_operations' => $totalOps,
                'hits' => $hitsCount,
                'misses' => $missesCount,
                'hit_rate' => $totalOps > 0 ? round(($hitsCount / $totalOps) * 100, 2) : 0,
                'miss_rate' => $totalOps > 0 ? round(($missesCount / $totalOps) * 100, 2) : 0,
                'avg_response_time' => $dayHits->avg('response_time') ? round($dayHits->avg('response_time'), 3) : 0
            ];
        }

        return $dailyStats;
    }

    /**
     * Ottiene le statistiche orarie
     */
    public static function getHourlyStats(int $hours = 24): array
    {
        $startDate = now()->subHours($hours);
        $endDate = now();

        $hits = static::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function($record) {
                return $record->created_at->format('Y-m-d H:00');
            });

        $hourlyStats = [];
        for ($i = $hours - 1; $i >= 0; $i--) {
            $hour = now()->subHours($i)->format('Y-m-d H:00');
            $hourHits = $hits->get($hour, collect());

            $totalOps = $hourHits->count();
            $hitsCount = $hourHits->where('type', 'hit')->count();
            $missesCount = $hourHits->where('type', 'miss')->count();

            $hourlyStats[$hour] = [
                'hour' => $hour,
                'total_operations' => $totalOps,
                'hits' => $hitsCount,
                'misses' => $missesCount,
                'hit_rate' => $totalOps > 0 ? round(($hitsCount / $totalOps) * 100, 2) : 0,
                'miss_rate' => $totalOps > 0 ? round(($missesCount / $totalOps) * 100, 2) : 0,
                'avg_response_time' => $hourHits->avg('response_time') ? round($hourHits->avg('response_time'), 3) : 0
            ];
        }

        return $hourlyStats;
    }

    /**
     * Ottiene le statistiche di performance
     */
    public static function getPerformanceStats(): array
    {
        $stats = static::selectRaw('
            AVG(response_time) as avg_response_time,
            MIN(response_time) as min_response_time,
            MAX(response_time) as max_response_time,
            PERCENTILE_CONT(0.5) WITHIN GROUP (ORDER BY response_time) as median_response_time,
            PERCENTILE_CONT(0.95) WITHIN GROUP (ORDER BY response_time) as p95_response_time,
            PERCENTILE_CONT(0.99) WITHIN GROUP (ORDER BY response_time) as p99_response_time
        ')->first();

        return [
            'avg_response_time' => round($stats->avg_response_time, 3),
            'min_response_time' => round($stats->min_response_time, 3),
            'max_response_time' => round($stats->max_response_time, 3),
            'median_response_time' => round($stats->median_response_time, 3),
            'p95_response_time' => round($stats->p95_response_time, 3),
            'p99_response_time' => round($stats->p99_response_time, 3)
        ];
    }

    /**
     * Ottiene le statistiche di hit rate
     */
    public static function getHitRateStats(): array
    {
        $stats = static::selectRaw('
            AVG(hit_rate) as avg_hit_rate,
            MIN(hit_rate) as min_hit_rate,
            MAX(hit_rate) as max_hit_rate,
            PERCENTILE_CONT(0.5) WITHIN GROUP (ORDER BY hit_rate) as median_hit_rate,
            PERCENTILE_CONT(0.95) WITHIN GROUP (ORDER BY hit_rate) as p95_hit_rate
        ')->first();

        return [
            'avg_hit_rate' => round($stats->avg_hit_rate, 2),
            'min_hit_rate' => round($stats->min_hit_rate, 2),
            'max_hit_rate' => round($stats->max_hit_rate, 2),
            'median_hit_rate' => round($stats->median_hit_rate, 2),
            'p95_hit_rate' => round($stats->p95_hit_rate, 2)
        ];
    }

    /**
     * Ottiene le statistiche per tempo di risposta
     */
    public static function getResponseTimeStats(): array
    {
        $ranges = [
            '0-10ms' => [0, 0.01],
            '10-50ms' => [0.01, 0.05],
            '50-100ms' => [0.05, 0.1],
            '100-500ms' => [0.1, 0.5],
            '500ms-1s' => [0.5, 1.0],
            '1s+' => [1.0, null]
        ];

        $stats = [];
        foreach ($ranges as $range => $limits) {
            $query = static::query();
            if ($limits[0] !== null) {
                $query->where('response_time', '>=', $limits[0]);
            }
            if ($limits[1] !== null) {
                $query->where('response_time', '<', $limits[1]);
            }

            $count = $query->count();
            $stats[$range] = [
                'range' => $range,
                'count' => $count,
                'percentage' => static::count() > 0 ? round(($count / static::count()) * 100, 2) : 0
            ];
        }

        return $stats;
    }

    /**
     * Ottiene le statistiche per strategia e tipo
     */
    public static function getStatsByStrategyAndType(): array
    {
        return static::select('strategy', 'type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('AVG(response_time) as avg_response_time')
            ->selectRaw('AVG(hit_rate) as avg_hit_rate')
            ->groupBy('strategy', 'type')
            ->get()
            ->map(function($stat) {
                return [
                    'strategy' => $stat->strategy,
                    'type' => $stat->type,
                    'count' => $stat->count,
                    'avg_response_time' => round($stat->avg_response_time, 3),
                    'avg_hit_rate' => round($stat->avg_hit_rate, 2)
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene le statistiche in tempo reale
     */
    public static function getRealTimeStats(): array
    {
        $lastHour = now()->subHour();
        
        $recentHits = static::where('created_at', '>=', $lastHour)->get();
        
        $totalOps = $recentHits->count();
        $hitsCount = $recentHits->where('type', 'hit')->count();
        $missesCount = $recentHits->where('type', 'miss')->count();

        return [
            'last_hour_operations' => $totalOps,
            'last_hour_hits' => $hitsCount,
            'last_hour_misses' => $missesCount,
            'last_hour_hit_rate' => $totalOps > 0 ? round(($hitsCount / $totalOps) * 100, 2) : 0,
            'last_hour_avg_response_time' => $recentHits->avg('response_time') ? round($recentHits->avg('response_time'), 3) : 0,
            'strategies_used' => $recentHits->pluck('strategy')->unique()->values()->toArray()
        ];
    }

    /**
     * Pulisce i dati vecchi
     */
    public static function cleanupOldData(int $days = 90): int
    {
        $cutoffDate = now()->subDays($days);
        return static::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Ottiene le statistiche di tendenza
     */
    public static function getTrendStats(): array
    {
        $last7Days = static::where('created_at', '>=', now()->subDays(7))->get();
        $previous7Days = static::whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->get();

        $last7DaysHits = $last7Days->where('type', 'hit')->count();
        $last7DaysTotal = $last7Days->count();
        $last7DaysHitRate = $last7DaysTotal > 0 ? ($last7DaysHits / $last7DaysTotal) * 100 : 0;

        $previous7DaysHits = $previous7Days->where('type', 'hit')->count();
        $previous7DaysTotal = $previous7Days->count();
        $previous7DaysHitRate = $previous7DaysTotal > 0 ? ($previous7DaysHits / $previous7DaysTotal) * 100 : 0;

        $hitRateChange = $last7DaysHitRate - $previous7DaysHitRate;
        $hitRateChangePercent = $previous7DaysHitRate > 0 ? ($hitRateChange / $previous7DaysHitRate) * 100 : 0;

        return [
            'current_hit_rate' => round($last7DaysHitRate, 2),
            'previous_hit_rate' => round($previous7DaysHitRate, 2),
            'hit_rate_change' => round($hitRateChange, 2),
            'hit_rate_change_percent' => round($hitRateChangePercent, 2),
            'trend_direction' => $hitRateChange > 0 ? 'up' : ($hitRateChange < 0 ? 'down' : 'stable')
        ];
    }
}
