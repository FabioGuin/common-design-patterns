<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FallbackLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'status',
        'provider',
        'strategy',
        'response_time',
        'context',
        'error_message',
        'retry_count',
        'fallback_used',
        'circuit_breaker_state'
    ];

    protected $casts = [
        'context' => 'array',
        'response_time' => 'float',
        'retry_count' => 'integer',
        'fallback_used' => 'boolean',
        'created_at' => 'datetime'
    ];

    /**
     * Scope per richieste di successo
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope per richieste fallite
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'error');
    }

    /**
     * Scope per richieste con fallback
     */
    public function scopeWithFallback($query)
    {
        return $query->where('status', 'fallback_success');
    }

    /**
     * Scope per provider specifico
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
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
     * Scope per richieste con retry
     */
    public function scopeWithRetry($query)
    {
        return $query->where('retry_count', '>', 0);
    }

    /**
     * Scope per richieste senza retry
     */
    public function scopeWithoutRetry($query)
    {
        return $query->where('retry_count', 0);
    }

    /**
     * Scope per circuit breaker aperto
     */
    public function scopeWithCircuitBreakerOpen($query)
    {
        return $query->where('circuit_breaker_state', 'open');
    }

    /**
     * Scope per circuit breaker chiuso
     */
    public function scopeWithCircuitBreakerClosed($query)
    {
        return $query->where('circuit_breaker_state', 'closed');
    }

    /**
     * Scope per richieste lente
     */
    public function scopeSlow($query, float $threshold = 5.0)
    {
        return $query->where('response_time', '>', $threshold);
    }

    /**
     * Scope per richieste veloci
     */
    public function scopeFast($query, float $threshold = 1.0)
    {
        return $query->where('response_time', '<=', $threshold);
    }

    /**
     * Verifica se la richiesta è stata di successo
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Verifica se la richiesta è fallita
     */
    public function isFailed(): bool
    {
        return $this->status === 'error';
    }

    /**
     * Verifica se è stata usata una strategia di fallback
     */
    public function usedFallback(): bool
    {
        return $this->fallback_used;
    }

    /**
     * Verifica se è stato usato il retry
     */
    public function usedRetry(): bool
    {
        return $this->retry_count > 0;
    }

    /**
     * Ottiene il tempo di risposta formattato
     */
    public function getFormattedResponseTimeAttribute(): string
    {
        if ($this->response_time < 1) {
            return round($this->response_time * 1000, 0) . 'ms';
        }
        return round($this->response_time, 2) . 's';
    }

    /**
     * Ottiene le informazioni di contesto
     */
    public function getContextInfoAttribute(): array
    {
        return [
            'prompt_length' => $this->context['prompt_length'] ?? 0,
            'max_tokens' => $this->context['max_tokens'] ?? null,
            'temperature' => $this->context['temperature'] ?? null,
            'tokens_used' => $this->context['tokens_used'] ?? 0,
            'model' => $this->context['model'] ?? null,
            'error_type' => $this->context['error_type'] ?? null,
            'fallback_strategy' => $this->context['fallback_strategy'] ?? null
        ];
    }

    /**
     * Ottiene le statistiche di performance
     */
    public function getPerformanceStatsAttribute(): array
    {
        return [
            'response_time' => $this->response_time,
            'formatted_response_time' => $this->formatted_response_time,
            'retry_count' => $this->retry_count,
            'fallback_used' => $this->fallback_used,
            'circuit_breaker_state' => $this->circuit_breaker_state,
            'is_successful' => $this->isSuccessful(),
            'is_failed' => $this->isFailed()
        ];
    }

    /**
     * Ottiene le statistiche aggregate
     */
    public static function getAggregateStats(): array
    {
        $stats = static::selectRaw('
            COUNT(*) as total_requests,
            COUNT(CASE WHEN status = "success" THEN 1 END) as successful_requests,
            COUNT(CASE WHEN status = "error" THEN 1 END) as failed_requests,
            COUNT(CASE WHEN status = "fallback_success" THEN 1 END) as fallback_requests,
            COUNT(CASE WHEN retry_count > 0 THEN 1 END) as retry_requests,
            COUNT(CASE WHEN fallback_used = 1 THEN 1 END) as fallback_used_requests,
            AVG(response_time) as avg_response_time,
            MIN(response_time) as min_response_time,
            MAX(response_time) as max_response_time,
            AVG(retry_count) as avg_retry_count
        ')->first();

        return [
            'total_requests' => $stats->total_requests,
            'successful_requests' => $stats->successful_requests,
            'failed_requests' => $stats->failed_requests,
            'fallback_requests' => $stats->fallback_requests,
            'retry_requests' => $stats->retry_requests,
            'fallback_used_requests' => $stats->fallback_used_requests,
            'success_rate' => $stats->total_requests > 0 ? round(($stats->successful_requests / $stats->total_requests) * 100, 2) : 0,
            'fallback_rate' => $stats->total_requests > 0 ? round(($stats->fallback_requests / $stats->total_requests) * 100, 2) : 0,
            'retry_rate' => $stats->total_requests > 0 ? round(($stats->retry_requests / $stats->total_requests) * 100, 2) : 0,
            'avg_response_time' => round($stats->avg_response_time, 3),
            'min_response_time' => round($stats->min_response_time, 3),
            'max_response_time' => round($stats->max_response_time, 3),
            'avg_retry_count' => round($stats->avg_retry_count, 2)
        ];
    }

    /**
     * Ottiene le statistiche per provider
     */
    public static function getStatsByProvider(): array
    {
        return static::select('provider')
            ->selectRaw('COUNT(*) as total_requests')
            ->selectRaw('COUNT(CASE WHEN status = "success" THEN 1 END) as successful')
            ->selectRaw('COUNT(CASE WHEN status = "error" THEN 1 END) as failed')
            ->selectRaw('COUNT(CASE WHEN status = "fallback_success" THEN 1 END) as fallback')
            ->selectRaw('AVG(response_time) as avg_response_time')
            ->selectRaw('AVG(retry_count) as avg_retry_count')
            ->groupBy('provider')
            ->get()
            ->map(function($stat) {
                $total = $stat->total_requests;
                return [
                    'provider' => $stat->provider,
                    'total_requests' => $total,
                    'successful' => $stat->successful,
                    'failed' => $stat->failed,
                    'fallback' => $stat->fallback,
                    'success_rate' => $total > 0 ? round(($stat->successful / $total) * 100, 2) : 0,
                    'fallback_rate' => $total > 0 ? round(($stat->fallback / $total) * 100, 2) : 0,
                    'avg_response_time' => round($stat->avg_response_time, 3),
                    'avg_retry_count' => round($stat->avg_retry_count, 2)
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene le statistiche per strategia
     */
    public static function getStatsByStrategy(): array
    {
        return static::select('strategy')
            ->selectRaw('COUNT(*) as total_requests')
            ->selectRaw('COUNT(CASE WHEN status = "success" THEN 1 END) as successful')
            ->selectRaw('COUNT(CASE WHEN status = "error" THEN 1 END) as failed')
            ->selectRaw('COUNT(CASE WHEN status = "fallback_success" THEN 1 END) as fallback')
            ->selectRaw('AVG(response_time) as avg_response_time')
            ->selectRaw('AVG(retry_count) as avg_retry_count')
            ->groupBy('strategy')
            ->get()
            ->map(function($stat) {
                $total = $stat->total_requests;
                return [
                    'strategy' => $stat->strategy,
                    'total_requests' => $total,
                    'successful' => $stat->successful,
                    'failed' => $stat->failed,
                    'fallback' => $stat->fallback,
                    'success_rate' => $total > 0 ? round(($stat->successful / $total) * 100, 2) : 0,
                    'fallback_rate' => $total > 0 ? round(($stat->fallback / $total) * 100, 2) : 0,
                    'avg_response_time' => round($stat->avg_response_time, 3),
                    'avg_retry_count' => round($stat->avg_retry_count, 2)
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

        $logs = static::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function($record) {
                return $record->created_at->format('Y-m-d');
            });

        $dailyStats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayLogs = $logs->get($date, collect());

            $totalRequests = $dayLogs->count();
            $successfulRequests = $dayLogs->where('status', 'success')->count();
            $failedRequests = $dayLogs->where('status', 'error')->count();
            $fallbackRequests = $dayLogs->where('status', 'fallback_success')->count();

            $dailyStats[$date] = [
                'date' => $date,
                'total_requests' => $totalRequests,
                'successful_requests' => $successfulRequests,
                'failed_requests' => $failedRequests,
                'fallback_requests' => $fallbackRequests,
                'success_rate' => $totalRequests > 0 ? round(($successfulRequests / $totalRequests) * 100, 2) : 0,
                'fallback_rate' => $totalRequests > 0 ? round(($fallbackRequests / $totalRequests) * 100, 2) : 0,
                'avg_response_time' => $dayLogs->avg('response_time') ? round($dayLogs->avg('response_time'), 3) : 0
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

        $logs = static::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function($record) {
                return $record->created_at->format('Y-m-d H:00');
            });

        $hourlyStats = [];
        for ($i = $hours - 1; $i >= 0; $i--) {
            $hour = now()->subHours($i)->format('Y-m-d H:00');
            $hourLogs = $logs->get($hour, collect());

            $totalRequests = $hourLogs->count();
            $successfulRequests = $hourLogs->where('status', 'success')->count();
            $failedRequests = $hourLogs->where('status', 'error')->count();
            $fallbackRequests = $hourLogs->where('status', 'fallback_success')->count();

            $hourlyStats[$hour] = [
                'hour' => $hour,
                'total_requests' => $totalRequests,
                'successful_requests' => $successfulRequests,
                'failed_requests' => $failedRequests,
                'fallback_requests' => $fallbackRequests,
                'success_rate' => $totalRequests > 0 ? round(($successfulRequests / $totalRequests) * 100, 2) : 0,
                'fallback_rate' => $totalRequests > 0 ? round(($fallbackRequests / $totalRequests) * 100, 2) : 0,
                'avg_response_time' => $hourLogs->avg('response_time') ? round($hourLogs->avg('response_time'), 3) : 0
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
     * Ottiene le statistiche di retry
     */
    public static function getRetryStats(): array
    {
        $stats = static::selectRaw('
            COUNT(CASE WHEN retry_count > 0 THEN 1 END) as retry_requests,
            AVG(retry_count) as avg_retry_count,
            MAX(retry_count) as max_retry_count,
            COUNT(CASE WHEN retry_count = 1 THEN 1 END) as single_retry,
            COUNT(CASE WHEN retry_count = 2 THEN 1 END) as double_retry,
            COUNT(CASE WHEN retry_count >= 3 THEN 1 END) as multiple_retry
        ')->first();

        $totalRequests = static::count();

        return [
            'retry_requests' => $stats->retry_requests,
            'retry_rate' => $totalRequests > 0 ? round(($stats->retry_requests / $totalRequests) * 100, 2) : 0,
            'avg_retry_count' => round($stats->avg_retry_count, 2),
            'max_retry_count' => $stats->max_retry_count,
            'single_retry' => $stats->single_retry,
            'double_retry' => $stats->double_retry,
            'multiple_retry' => $stats->multiple_retry
        ];
    }

    /**
     * Pulisce i log vecchi
     */
    public static function cleanupOldLogs(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        return static::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Ottiene le richieste problematiche
     */
    public static function getProblematicRequests(int $limit = 20): array
    {
        return static::where(function($query) {
            $query->where('status', 'error')
                  ->orWhere('response_time', '>', 10)
                  ->orWhere('retry_count', '>=', 3);
        })
        ->orderBy('response_time', 'desc')
        ->orderBy('retry_count', 'desc')
        ->limit($limit)
        ->get()
        ->map(function($log) {
            return [
                'request_id' => $log->request_id,
                'status' => $log->status,
                'provider' => $log->provider,
                'strategy' => $log->strategy,
                'response_time' => $log->formatted_response_time,
                'retry_count' => $log->retry_count,
                'fallback_used' => $log->fallback_used,
                'error_message' => $log->error_message,
                'created_at' => $log->created_at,
                'issues' => array_filter([
                    $log->status === 'error' ? 'failed' : null,
                    $log->response_time > 10 ? 'slow' : null,
                    $log->retry_count >= 3 ? 'high_retry' : null
                ])
            ];
        })
        ->toArray();
    }
}
