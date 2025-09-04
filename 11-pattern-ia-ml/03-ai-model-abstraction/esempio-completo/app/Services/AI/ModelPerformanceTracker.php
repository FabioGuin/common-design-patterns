<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\ModelPerformance;
use App\Models\ModelUsage;

class ModelPerformanceTracker
{
    private array $config;

    public function __construct()
    {
        $this->config = config('ai_models', []);
    }

    /**
     * Traccia l'utilizzo di un modello
     */
    public function trackUsage(AIModelInterface $model, array $response, bool $success): void
    {
        if (!$this->config['performance_tracking']['enabled']) {
            return;
        }

        try {
            $duration = $response['duration'] ?? 0;
            $cost = $response['cost'] ?? 0;
            $tokensUsed = $response['tokens_used'] ?? 0;

            // Aggiorna le performance del modello
            $this->updateModelPerformance($model->getName(), $duration, $success, $cost, $tokensUsed);

            // Controlla se ci sono alert da inviare
            $this->checkAlerts($model->getName(), $duration, $success, $cost);

            // Aggiorna le statistiche in cache
            $this->updateCachedStats($model->getName(), $duration, $success, $cost);

        } catch (\Exception $e) {
            Log::error('Failed to track model usage', [
                'model' => $model->getName(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Traccia un errore
     */
    public function trackError(string $error, float $duration = 0): void
    {
        if (!$this->config['performance_tracking']['enabled']) {
            return;
        }

        try {
            // Salva l'errore nel database
            ModelUsage::create([
                'request_id' => uniqid(),
                'model_name' => 'unknown',
                'provider' => 'unknown',
                'prompt' => '',
                'response' => ['error' => $error],
                'success' => false,
                'duration' => $duration,
                'cost' => 0,
                'tokens_used' => 0
            ]);

            Log::error('AI Model Error Tracked', [
                'error' => $error,
                'duration' => $duration
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to track error', [
                'error' => $error,
                'tracking_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Aggiorna le performance di un modello
     */
    private function updateModelPerformance(string $modelName, float $duration, bool $success, float $cost, int $tokensUsed): void
    {
        try {
            $performance = ModelPerformance::where('model_name', $modelName)->first();
            
            if (!$performance) {
                $performance = ModelPerformance::create([
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

            // Aggiorna le statistiche
            $performance->total_requests++;
            $performance->total_duration += $duration;
            $performance->total_cost += $cost;
            $performance->total_tokens += $tokensUsed;
            $performance->last_used_at = now();

            if ($success) {
                $performance->successful_requests++;
            }

            // Calcola le medie
            $performance->average_response_time = $performance->total_duration / $performance->total_requests;
            $performance->success_rate = ($performance->successful_requests / $performance->total_requests) * 100;
            $performance->average_cost = $performance->total_cost / $performance->total_requests;

            $performance->save();

        } catch (\Exception $e) {
            Log::error('Failed to update model performance', [
                'model' => $modelName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Controlla se ci sono alert da inviare
     */
    private function checkAlerts(string $modelName, float $duration, bool $success, float $cost): void
    {
        $alerts = $this->config['performance_tracking']['alerts'] ?? [];
        
        // Alert per error rate alto
        if (isset($alerts['high_error_rate'])) {
            $errorRate = $this->getErrorRate($modelName);
            if ($errorRate > $alerts['high_error_rate']) {
                $this->sendAlert('high_error_rate', [
                    'model' => $modelName,
                    'error_rate' => $errorRate,
                    'threshold' => $alerts['high_error_rate']
                ]);
            }
        }

        // Alert per risposta lenta
        if (isset($alerts['slow_response']) && $duration > $alerts['slow_response']) {
            $this->sendAlert('slow_response', [
                'model' => $modelName,
                'duration' => $duration,
                'threshold' => $alerts['slow_response']
            ]);
        }

        // Alert per costo alto
        if (isset($alerts['high_cost']) && $cost > $alerts['high_cost']) {
            $this->sendAlert('high_cost', [
                'model' => $modelName,
                'cost' => $cost,
                'threshold' => $alerts['high_cost']
            ]);
        }
    }

    /**
     * Invia un alert
     */
    private function sendAlert(string $type, array $data): void
    {
        Log::warning("AI Model Alert: {$type}", $data);
        
        // Qui potresti implementare l'invio di email, notifiche, ecc.
        // Per ora logghiamo solo l'alert
    }

    /**
     * Aggiorna le statistiche in cache
     */
    private function updateCachedStats(string $modelName, float $duration, bool $success, float $cost): void
    {
        $cacheKey = "ai_model_stats_{$modelName}";
        
        $stats = Cache::get($cacheKey, [
            'total_requests' => 0,
            'successful_requests' => 0,
            'total_duration' => 0,
            'total_cost' => 0,
            'average_response_time' => 0,
            'success_rate' => 0,
            'last_updated' => now()->toISOString()
        ]);

        $stats['total_requests']++;
        $stats['total_duration'] += $duration;
        $stats['total_cost'] += $cost;
        $stats['last_updated'] = now()->toISOString();

        if ($success) {
            $stats['successful_requests']++;
        }

        $stats['average_response_time'] = $stats['total_duration'] / $stats['total_requests'];
        $stats['success_rate'] = ($stats['successful_requests'] / $stats['total_requests']) * 100;

        Cache::put($cacheKey, $stats, 3600); // 1 ora
    }

    /**
     * Ottiene il tasso di errore per un modello
     */
    private function getErrorRate(string $modelName): float
    {
        $performance = ModelPerformance::where('model_name', $modelName)->first();
        
        if (!$performance || $performance->total_requests == 0) {
            return 0;
        }

        return (($performance->total_requests - $performance->successful_requests) / $performance->total_requests) * 100;
    }

    /**
     * Ottiene le performance di un modello
     */
    public function getModelPerformance(string $modelName): ?array
    {
        $performance = ModelPerformance::where('model_name', $modelName)->first();
        
        if (!$performance) {
            return null;
        }

        return [
            'model_name' => $performance->model_name,
            'total_requests' => $performance->total_requests,
            'successful_requests' => $performance->successful_requests,
            'success_rate' => round($performance->success_rate, 2),
            'average_response_time' => round($performance->average_response_time, 2),
            'total_duration' => round($performance->total_duration, 2),
            'average_cost' => round($performance->average_cost, 4),
            'total_cost' => round($performance->total_cost, 4),
            'total_tokens' => $performance->total_tokens,
            'last_used_at' => $performance->last_used_at
        ];
    }

    /**
     * Ottiene le performance di tutti i modelli
     */
    public function getAllModelsPerformance(): array
    {
        $performances = ModelPerformance::all();
        
        return $performances->map(function($performance) {
            return $this->getModelPerformance($performance->model_name);
        })->filter()->values()->toArray();
    }

    /**
     * Ottiene le statistiche aggregate
     */
    public function getAggregateStats(): array
    {
        $totalRequests = ModelUsage::count();
        $successfulRequests = ModelUsage::where('success', true)->count();
        $totalCost = ModelUsage::sum('cost');
        $averageDuration = ModelUsage::avg('duration');

        return [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'success_rate' => $totalRequests > 0 ? round(($successfulRequests / $totalRequests) * 100, 2) : 0,
            'total_cost' => round($totalCost, 4),
            'average_cost' => $totalRequests > 0 ? round($totalCost / $totalRequests, 4) : 0,
            'average_duration' => round($averageDuration, 2),
            'models_count' => ModelPerformance::count()
        ];
    }

    /**
     * Ottiene le performance per periodo
     */
    public function getPerformanceByPeriod(string $modelName = null, int $days = 7): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $query = ModelUsage::whereBetween('created_at', [$startDate, $endDate]);
        
        if ($modelName) {
            $query->where('model_name', $modelName);
        }

        $usage = $query->get();
        
        $dailyStats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayUsage = $usage->filter(function($record) use ($date) {
                return $record->created_at->format('Y-m-d') === $date;
            });

            $dailyStats[$date] = [
                'date' => $date,
                'total_requests' => $dayUsage->count(),
                'successful_requests' => $dayUsage->where('success', true)->count(),
                'success_rate' => $dayUsage->count() > 0 ? round(($dayUsage->where('success', true)->count() / $dayUsage->count()) * 100, 2) : 0,
                'total_cost' => round($dayUsage->sum('cost'), 4),
                'average_duration' => round($dayUsage->avg('duration'), 2)
            ];
        }

        return $dailyStats;
    }

    /**
     * Ottiene i modelli più utilizzati
     */
    public function getMostUsedModels(int $limit = 10): array
    {
        $models = ModelUsage::selectRaw('model_name, COUNT(*) as usage_count, AVG(duration) as avg_duration, SUM(cost) as total_cost')
            ->groupBy('model_name')
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get();

        return $models->map(function($model) {
            return [
                'model_name' => $model->model_name,
                'usage_count' => $model->usage_count,
                'avg_duration' => round($model->avg_duration, 2),
                'total_cost' => round($model->total_cost, 4)
            ];
        })->toArray();
    }

    /**
     * Ottiene i modelli con le migliori performance
     */
    public function getBestPerformingModels(int $limit = 10): array
    {
        $models = ModelPerformance::orderBy('success_rate', 'desc')
            ->orderBy('average_response_time', 'asc')
            ->limit($limit)
            ->get();

        return $models->map(function($model) {
            return [
                'model_name' => $model->model_name,
                'success_rate' => round($model->success_rate, 2),
                'average_response_time' => round($model->average_response_time, 2),
                'total_requests' => $model->total_requests,
                'average_cost' => round($model->average_cost, 4)
            ];
        })->toArray();
    }

    /**
     * Pulisce i dati vecchi
     */
    public function cleanupOldData(int $days = 90): void
    {
        $cutoffDate = now()->subDays($days);
        
        // Pulisce i dati di utilizzo vecchi
        ModelUsage::where('created_at', '<', $cutoffDate)->delete();
        
        Log::info('AI Model performance data cleaned up', [
            'cutoff_date' => $cutoffDate,
            'days_retained' => $days
        ]);
    }

    /**
     * Ottiene le metriche in tempo reale
     */
    public function getRealTimeMetrics(): array
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
