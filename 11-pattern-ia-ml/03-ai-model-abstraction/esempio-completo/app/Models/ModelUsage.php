<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'model_name',
        'provider',
        'prompt',
        'response',
        'success',
        'duration',
        'cost',
        'tokens_used',
        'error_message',
        'metadata'
    ];

    protected $casts = [
        'response' => 'array',
        'success' => 'boolean',
        'duration' => 'float',
        'cost' => 'float',
        'tokens_used' => 'integer',
        'metadata' => 'array'
    ];

    /**
     * Scope per richieste di successo
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope per richieste fallite
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope per modello specifico
     */
    public function scopeByModel($query, string $modelName)
    {
        return $query->where('model_name', $modelName);
    }

    /**
     * Scope per provider specifico
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope per periodo
     */
    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope per durata
     */
    public function scopeByDuration($query, float $minDuration, float $maxDuration = null)
    {
        if ($maxDuration) {
            return $query->whereBetween('duration', [$minDuration, $maxDuration]);
        }
        return $query->where('duration', '>=', $minDuration);
    }

    /**
     * Scope per costo
     */
    public function scopeByCost($query, float $minCost, float $maxCost = null)
    {
        if ($maxCost) {
            return $query->whereBetween('cost', [$minCost, $maxCost]);
        }
        return $query->where('cost', '>=', $minCost);
    }

    /**
     * Scope per token utilizzati
     */
    public function scopeByTokens($query, int $minTokens, int $maxTokens = null)
    {
        if ($maxTokens) {
            return $query->whereBetween('tokens_used', [$minTokens, $maxTokens]);
        }
        return $query->where('tokens_used', '>=', $minTokens);
    }

    /**
     * Scope per richieste con errori
     */
    public function scopeWithErrors($query)
    {
        return $query->whereNotNull('error_message');
    }

    /**
     * Scope per richieste senza errori
     */
    public function scopeWithoutErrors($query)
    {
        return $query->whereNull('error_message');
    }

    /**
     * Ottiene il modello associato
     */
    public function model()
    {
        return $this->belongsTo(AIModel::class, 'model_name', 'name');
    }

    /**
     * Ottiene le statistiche per modello
     */
    public static function getStatsByModel(string $modelName = null): array
    {
        $query = static::query();
        
        if ($modelName) {
            $query->where('model_name', $modelName);
        }

        $totalRequests = $query->count();
        $successfulRequests = $query->where('success', true)->count();
        $totalCost = $query->sum('cost');
        $totalDuration = $query->sum('duration');
        $totalTokens = $query->sum('tokens_used');

        return [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'failed_requests' => $totalRequests - $successfulRequests,
            'success_rate' => $totalRequests > 0 ? round(($successfulRequests / $totalRequests) * 100, 2) : 0,
            'total_cost' => round($totalCost, 4),
            'average_cost' => $totalRequests > 0 ? round($totalCost / $totalRequests, 4) : 0,
            'total_duration' => round($totalDuration, 2),
            'average_duration' => $totalRequests > 0 ? round($totalDuration / $totalRequests, 2) : 0,
            'total_tokens' => $totalTokens,
            'average_tokens' => $totalRequests > 0 ? round($totalTokens / $totalRequests, 2) : 0
        ];
    }

    /**
     * Ottiene le statistiche per provider
     */
    public static function getStatsByProvider(string $provider = null): array
    {
        $query = static::query();
        
        if ($provider) {
            $query->where('provider', $provider);
        }

        $totalRequests = $query->count();
        $successfulRequests = $query->where('success', true)->count();
        $totalCost = $query->sum('cost');
        $totalDuration = $query->sum('duration');

        return [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'success_rate' => $totalRequests > 0 ? round(($successfulRequests / $totalRequests) * 100, 2) : 0,
            'total_cost' => round($totalCost, 4),
            'average_cost' => $totalRequests > 0 ? round($totalCost / $totalRequests, 4) : 0,
            'total_duration' => round($totalDuration, 2),
            'average_duration' => $totalRequests > 0 ? round($totalDuration / $totalRequests, 2) : 0
        ];
    }

    /**
     * Ottiene le statistiche per periodo
     */
    public static function getStatsByPeriod($startDate, $endDate): array
    {
        $query = static::whereBetween('created_at', [$startDate, $endDate]);

        $totalRequests = $query->count();
        $successfulRequests = $query->where('success', true)->count();
        $totalCost = $query->sum('cost');
        $totalDuration = $query->sum('duration');

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate
            ],
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'success_rate' => $totalRequests > 0 ? round(($successfulRequests / $totalRequests) * 100, 2) : 0,
            'total_cost' => round($totalCost, 4),
            'average_cost' => $totalRequests > 0 ? round($totalCost / $totalRequests, 4) : 0,
            'total_duration' => round($totalDuration, 2),
            'average_duration' => $totalRequests > 0 ? round($totalDuration / $totalRequests, 2) : 0
        ];
    }

    /**
     * Ottiene le statistiche giornaliere
     */
    public static function getDailyStats(int $days = 7): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $usage = static::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function($record) {
                return $record->created_at->format('Y-m-d');
            });

        $dailyStats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayUsage = $usage->get($date, collect());

            $dailyStats[$date] = [
                'date' => $date,
                'total_requests' => $dayUsage->count(),
                'successful_requests' => $dayUsage->where('success', true)->count(),
                'success_rate' => $dayUsage->count() > 0 ? round(($dayUsage->where('success', true)->count() / $dayUsage->count()) * 100, 2) : 0,
                'total_cost' => round($dayUsage->sum('cost'), 4),
                'average_cost' => $dayUsage->count() > 0 ? round($dayUsage->sum('cost') / $dayUsage->count(), 4) : 0,
                'total_duration' => round($dayUsage->sum('duration'), 2),
                'average_duration' => $dayUsage->count() > 0 ? round($dayUsage->sum('duration') / $dayUsage->count(), 2) : 0
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

        $usage = static::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function($record) {
                return $record->created_at->format('Y-m-d H:00');
            });

        $hourlyStats = [];
        for ($i = $hours - 1; $i >= 0; $i--) {
            $hour = now()->subHours($i)->format('Y-m-d H:00');
            $hourUsage = $usage->get($hour, collect());

            $hourlyStats[$hour] = [
                'hour' => $hour,
                'total_requests' => $hourUsage->count(),
                'successful_requests' => $hourUsage->where('success', true)->count(),
                'success_rate' => $hourUsage->count() > 0 ? round(($hourUsage->where('success', true)->count() / $hourUsage->count()) * 100, 2) : 0,
                'total_cost' => round($hourUsage->sum('cost'), 4),
                'average_cost' => $hourUsage->count() > 0 ? round($hourUsage->sum('cost') / $hourUsage->count(), 4) : 0,
                'total_duration' => round($hourUsage->sum('duration'), 2),
                'average_duration' => $hourUsage->count() > 0 ? round($hourUsage->sum('duration') / $hourUsage->count(), 2) : 0
            ];
        }

        return $hourlyStats;
    }

    /**
     * Ottiene i modelli più utilizzati
     */
    public static function getMostUsedModels(int $limit = 10): array
    {
        return static::select('model_name')
            ->selectRaw('COUNT(*) as usage_count')
            ->selectRaw('AVG(duration) as avg_duration')
            ->selectRaw('SUM(cost) as total_cost')
            ->selectRaw('AVG(CASE WHEN success = 1 THEN 1 ELSE 0 END) * 100 as success_rate')
            ->groupBy('model_name')
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($model) {
                return [
                    'model_name' => $model->model_name,
                    'usage_count' => $model->usage_count,
                    'avg_duration' => round($model->avg_duration, 2),
                    'total_cost' => round($model->total_cost, 4),
                    'success_rate' => round($model->success_rate, 2)
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene i provider più utilizzati
     */
    public static function getMostUsedProviders(int $limit = 10): array
    {
        return static::select('provider')
            ->selectRaw('COUNT(*) as usage_count')
            ->selectRaw('AVG(duration) as avg_duration')
            ->selectRaw('SUM(cost) as total_cost')
            ->selectRaw('AVG(CASE WHEN success = 1 THEN 1 ELSE 0 END) * 100 as success_rate')
            ->groupBy('provider')
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($provider) {
                return [
                    'provider' => $provider->provider,
                    'usage_count' => $provider->usage_count,
                    'avg_duration' => round($provider->avg_duration, 2),
                    'total_cost' => round($provider->total_cost, 4),
                    'success_rate' => round($provider->success_rate, 2)
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene le statistiche di errore
     */
    public static function getErrorStats(): array
    {
        $errors = static::whereNotNull('error_message')
            ->select('error_message')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('error_message')
            ->orderBy('count', 'desc')
            ->get();

        return $errors->map(function($error) {
            return [
                'error_message' => $error->error_message,
                'count' => $error->count
            ];
        })->toArray();
    }

    /**
     * Ottiene le statistiche di performance
     */
    public static function getPerformanceStats(): array
    {
        $stats = static::selectRaw('
            COUNT(*) as total_requests,
            AVG(CASE WHEN success = 1 THEN 1 ELSE 0 END) * 100 as success_rate,
            AVG(duration) as avg_duration,
            MIN(duration) as min_duration,
            MAX(duration) as max_duration,
            AVG(cost) as avg_cost,
            SUM(cost) as total_cost,
            AVG(tokens_used) as avg_tokens,
            SUM(tokens_used) as total_tokens
        ')->first();

        return [
            'total_requests' => $stats->total_requests,
            'success_rate' => round($stats->success_rate, 2),
            'avg_duration' => round($stats->avg_duration, 2),
            'min_duration' => round($stats->min_duration, 2),
            'max_duration' => round($stats->max_duration, 2),
            'avg_cost' => round($stats->avg_cost, 4),
            'total_cost' => round($stats->total_cost, 4),
            'avg_tokens' => round($stats->avg_tokens, 2),
            'total_tokens' => $stats->total_tokens
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
     * Ottiene le statistiche in tempo reale
     */
    public static function getRealTimeStats(): array
    {
        $lastHour = now()->subHour();
        
        $recentUsage = static::where('created_at', '>=', $lastHour)->get();
        
        return [
            'requests_last_hour' => $recentUsage->count(),
            'successful_requests_last_hour' => $recentUsage->where('success', true)->count(),
            'success_rate_last_hour' => $recentUsage->count() > 0 ? round(($recentUsage->where('success', true)->count() / $recentUsage->count()) * 100, 2) : 0,
            'cost_last_hour' => round($recentUsage->sum('cost'), 4),
            'average_duration_last_hour' => round($recentUsage->avg('duration'), 2),
            'models_used_last_hour' => $recentUsage->pluck('model_name')->unique()->values()->toArray()
        ];
    }
}
