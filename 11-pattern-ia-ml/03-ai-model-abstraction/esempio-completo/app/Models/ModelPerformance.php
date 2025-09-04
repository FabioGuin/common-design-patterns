<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelPerformance extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_name',
        'total_requests',
        'successful_requests',
        'total_duration',
        'total_cost',
        'total_tokens',
        'average_response_time',
        'success_rate',
        'average_cost',
        'last_used_at',
        'metadata'
    ];

    protected $casts = [
        'total_requests' => 'integer',
        'successful_requests' => 'integer',
        'total_duration' => 'float',
        'total_cost' => 'float',
        'total_tokens' => 'integer',
        'average_response_time' => 'float',
        'success_rate' => 'float',
        'average_cost' => 'float',
        'last_used_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Scope per modelli con performance migliori
     */
    public function scopeBestPerforming($query)
    {
        return $query->orderBy('success_rate', 'desc')
                    ->orderBy('average_response_time', 'asc');
    }

    /**
     * Scope per modelli più veloci
     */
    public function scopeFastest($query)
    {
        return $query->orderBy('average_response_time', 'asc');
    }

    /**
     * Scope per modelli più economici
     */
    public function scopeCheapest($query)
    {
        return $query->orderBy('average_cost', 'asc');
    }

    /**
     * Scope per modelli più affidabili
     */
    public function scopeMostReliable($query)
    {
        return $query->orderBy('success_rate', 'desc');
    }

    /**
     * Scope per modelli più utilizzati
     */
    public function scopeMostUsed($query)
    {
        return $query->orderBy('total_requests', 'desc');
    }

    /**
     * Scope per modelli con success rate minimo
     */
    public function scopeWithMinSuccessRate($query, float $minRate)
    {
        return $query->where('success_rate', '>=', $minRate);
    }

    /**
     * Scope per modelli con durata massima
     */
    public function scopeWithMaxDuration($query, float $maxDuration)
    {
        return $query->where('average_response_time', '<=', $maxDuration);
    }

    /**
     * Scope per modelli con costo massimo
     */
    public function scopeWithMaxCost($query, float $maxCost)
    {
        return $query->where('average_cost', '<=', $maxCost);
    }

    /**
     * Scope per modelli utilizzati di recente
     */
    public function scopeRecentlyUsed($query, int $hours = 24)
    {
        return $query->where('last_used_at', '>=', now()->subHours($hours));
    }

    /**
     * Ottiene il modello associato
     */
    public function model()
    {
        return $this->belongsTo(AIModel::class, 'model_name', 'name');
    }

    /**
     * Ottiene le statistiche aggregate
     */
    public static function getAggregateStats(): array
    {
        $stats = static::selectRaw('
            COUNT(*) as total_models,
            SUM(total_requests) as total_requests,
            SUM(successful_requests) as total_successful_requests,
            AVG(success_rate) as avg_success_rate,
            AVG(average_response_time) as avg_response_time,
            SUM(total_cost) as total_cost,
            AVG(average_cost) as avg_cost,
            SUM(total_tokens) as total_tokens
        ')->first();

        return [
            'total_models' => $stats->total_models,
            'total_requests' => $stats->total_requests,
            'total_successful_requests' => $stats->total_successful_requests,
            'overall_success_rate' => round($stats->avg_success_rate, 2),
            'avg_response_time' => round($stats->avg_response_time, 2),
            'total_cost' => round($stats->total_cost, 4),
            'avg_cost' => round($stats->avg_cost, 4),
            'total_tokens' => $stats->total_tokens
        ];
    }

    /**
     * Ottiene le statistiche per provider
     */
    public static function getStatsByProvider(): array
    {
        return static::join('ai_models', 'model_performances.model_name', '=', 'ai_models.name')
            ->select('ai_models.provider')
            ->selectRaw('COUNT(*) as model_count')
            ->selectRaw('SUM(total_requests) as total_requests')
            ->selectRaw('AVG(success_rate) as avg_success_rate')
            ->selectRaw('AVG(average_response_time) as avg_response_time')
            ->selectRaw('SUM(total_cost) as total_cost')
            ->selectRaw('AVG(average_cost) as avg_cost')
            ->groupBy('ai_models.provider')
            ->get()
            ->map(function($provider) {
                return [
                    'provider' => $provider->provider,
                    'model_count' => $provider->model_count,
                    'total_requests' => $provider->total_requests,
                    'avg_success_rate' => round($provider->avg_success_rate, 2),
                    'avg_response_time' => round($provider->avg_response_time, 2),
                    'total_cost' => round($provider->total_cost, 4),
                    'avg_cost' => round($provider->avg_cost, 4)
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene i modelli con le migliori performance
     */
    public static function getBestPerformingModels(int $limit = 10): array
    {
        return static::bestPerforming()
            ->limit($limit)
            ->get()
            ->map(function($model) {
                return [
                    'model_name' => $model->model_name,
                    'success_rate' => $model->success_rate,
                    'average_response_time' => $model->average_response_time,
                    'total_requests' => $model->total_requests,
                    'average_cost' => $model->average_cost,
                    'last_used_at' => $model->last_used_at
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene i modelli più veloci
     */
    public static function getFastestModels(int $limit = 10): array
    {
        return static::fastest()
            ->limit($limit)
            ->get()
            ->map(function($model) {
                return [
                    'model_name' => $model->model_name,
                    'average_response_time' => $model->average_response_time,
                    'success_rate' => $model->success_rate,
                    'total_requests' => $model->total_requests,
                    'average_cost' => $model->average_cost
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene i modelli più economici
     */
    public static function getCheapestModels(int $limit = 10): array
    {
        return static::cheapest()
            ->limit($limit)
            ->get()
            ->map(function($model) {
                return [
                    'model_name' => $model->model_name,
                    'average_cost' => $model->average_cost,
                    'success_rate' => $model->success_rate,
                    'average_response_time' => $model->average_response_time,
                    'total_requests' => $model->total_requests
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene i modelli più affidabili
     */
    public static function getMostReliableModels(int $limit = 10): array
    {
        return static::mostReliable()
            ->limit($limit)
            ->get()
            ->map(function($model) {
                return [
                    'model_name' => $model->model_name,
                    'success_rate' => $model->success_rate,
                    'average_response_time' => $model->average_response_time,
                    'total_requests' => $model->total_requests,
                    'average_cost' => $model->average_cost
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene i modelli più utilizzati
     */
    public static function getMostUsedModels(int $limit = 10): array
    {
        return static::mostUsed()
            ->limit($limit)
            ->get()
            ->map(function($model) {
                return [
                    'model_name' => $model->model_name,
                    'total_requests' => $model->total_requests,
                    'success_rate' => $model->success_rate,
                    'average_response_time' => $model->average_response_time,
                    'average_cost' => $model->average_cost
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene le statistiche di performance per periodo
     */
    public static function getPerformanceByPeriod($startDate, $endDate): array
    {
        $usage = ModelUsage::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                model_name,
                COUNT(*) as total_requests,
                AVG(CASE WHEN success = 1 THEN 1 ELSE 0 END) * 100 as success_rate,
                AVG(duration) as avg_response_time,
                SUM(cost) as total_cost,
                AVG(cost) as avg_cost,
                SUM(tokens_used) as total_tokens
            ')
            ->groupBy('model_name')
            ->get();

        return $usage->map(function($model) {
            return [
                'model_name' => $model->model_name,
                'total_requests' => $model->total_requests,
                'success_rate' => round($model->success_rate, 2),
                'avg_response_time' => round($model->avg_response_time, 2),
                'total_cost' => round($model->total_cost, 4),
                'avg_cost' => round($model->avg_cost, 4),
                'total_tokens' => $model->total_tokens
            ];
        })->toArray();
    }

    /**
     * Ottiene le statistiche di performance giornaliere
     */
    public static function getDailyPerformance(int $days = 7): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $usage = ModelUsage::whereBetween('created_at', [$startDate, $endDate])
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
     * Ottiene le statistiche di performance orarie
     */
    public static function getHourlyPerformance(int $hours = 24): array
    {
        $startDate = now()->subHours($hours);
        $endDate = now();

        $usage = ModelUsage::whereBetween('created_at', [$startDate, $endDate])
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
     * Ottiene le statistiche di performance per modello
     */
    public static function getModelPerformance(string $modelName): ?array
    {
        $performance = static::where('model_name', $modelName)->first();
        
        if (!$performance) {
            return null;
        }

        return [
            'model_name' => $performance->model_name,
            'total_requests' => $performance->total_requests,
            'successful_requests' => $performance->successful_requests,
            'success_rate' => $performance->success_rate,
            'average_response_time' => $performance->average_response_time,
            'total_duration' => $performance->total_duration,
            'average_cost' => $performance->average_cost,
            'total_cost' => $performance->total_cost,
            'total_tokens' => $performance->total_tokens,
            'last_used_at' => $performance->last_used_at
        ];
    }

    /**
     * Ottiene le statistiche di performance per provider
     */
    public static function getProviderPerformance(string $provider): array
    {
        return static::join('ai_models', 'model_performances.model_name', '=', 'ai_models.name')
            ->where('ai_models.provider', $provider)
            ->selectRaw('
                ai_models.provider,
                COUNT(*) as model_count,
                SUM(total_requests) as total_requests,
                AVG(success_rate) as avg_success_rate,
                AVG(average_response_time) as avg_response_time,
                SUM(total_cost) as total_cost,
                AVG(average_cost) as avg_cost
            ')
            ->groupBy('ai_models.provider')
            ->first();

        if (!$performance) {
            return [];
        }

        return [
            'provider' => $performance->provider,
            'model_count' => $performance->model_count,
            'total_requests' => $performance->total_requests,
            'avg_success_rate' => round($performance->avg_success_rate, 2),
            'avg_response_time' => round($performance->avg_response_time, 2),
            'total_cost' => round($performance->total_cost, 4),
            'avg_cost' => round($performance->avg_cost, 4)
        ];
    }

    /**
     * Aggiorna le performance di un modello
     */
    public static function updateModelPerformance(string $modelName, float $duration, bool $success, float $cost = 0, int $tokens = 0): void
    {
        $performance = static::where('model_name', $modelName)->first();
        
        if (!$performance) {
            $performance = static::create([
                'model_name' => $modelName,
                'total_requests' => 0,
                'successful_requests' => 0,
                'total_duration' => 0,
                'total_cost' => 0,
                'total_tokens' => 0,
                'average_response_time' => 0,
                'success_rate' => 0,
                'average_cost' => 0,
                'last_used_at' => now()
            ]);
        }

        $performance->total_requests++;
        $performance->total_duration += $duration;
        $performance->total_cost += $cost;
        $performance->total_tokens += $tokens;
        $performance->last_used_at = now();

        if ($success) {
            $performance->successful_requests++;
        }

        $performance->average_response_time = $performance->total_duration / $performance->total_requests;
        $performance->success_rate = ($performance->successful_requests / $performance->total_requests) * 100;
        $performance->average_cost = $performance->total_cost / $performance->total_requests;

        $performance->save();
    }

    /**
     * Pulisce i dati vecchi
     */
    public static function cleanupOldData(int $days = 90): int
    {
        $cutoffDate = now()->subDays($days);
        return static::where('last_used_at', '<', $cutoffDate)->delete();
    }

    /**
     * Ottiene le statistiche in tempo reale
     */
    public static function getRealTimeStats(): array
    {
        $lastHour = now()->subHour();
        
        $recentUsage = ModelUsage::where('created_at', '>=', $lastHour)->get();
        
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
