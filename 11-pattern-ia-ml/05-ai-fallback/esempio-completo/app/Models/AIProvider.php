<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'api_key',
        'base_url',
        'priority',
        'timeout',
        'retry_attempts',
        'enabled',
        'health_status',
        'last_health_check',
        'failure_count',
        'success_count',
        'average_response_time',
        'metadata'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'last_health_check' => 'datetime',
        'metadata' => 'array',
        'priority' => 'integer',
        'timeout' => 'integer',
        'retry_attempts' => 'integer',
        'failure_count' => 'integer',
        'success_count' => 'integer',
        'average_response_time' => 'float'
    ];

    /**
     * Scope per provider abilitati
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope per provider disabilitati
     */
    public function scopeDisabled($query)
    {
        return $query->where('enabled', false);
    }

    /**
     * Scope per provider sani
     */
    public function scopeHealthy($query)
    {
        return $query->where('health_status', 'healthy');
    }

    /**
     * Scope per provider non sani
     */
    public function scopeUnhealthy($query)
    {
        return $query->where('health_status', 'unhealthy');
    }

    /**
     * Scope per provider ordinati per priorità
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    /**
     * Scope per provider con alta priorità
     */
    public function scopeHighPriority($query, int $threshold = 3)
    {
        return $query->where('priority', '<=', $threshold);
    }

    /**
     * Scope per provider con bassa priorità
     */
    public function scopeLowPriority($query, int $threshold = 7)
    {
        return $query->where('priority', '>=', $threshold);
    }

    /**
     * Scope per provider con molti fallimenti
     */
    public function scopeHighFailure($query, int $threshold = 10)
    {
        return $query->where('failure_count', '>=', $threshold);
    }

    /**
     * Scope per provider con molti successi
     */
    public function scopeHighSuccess($query, int $threshold = 100)
    {
        return $query->where('success_count', '>=', $threshold);
    }

    /**
     * Scope per provider con tempo di risposta lento
     */
    public function scopeSlowResponse($query, float $threshold = 5.0)
    {
        return $query->where('average_response_time', '>', $threshold);
    }

    /**
     * Scope per provider con tempo di risposta veloce
     */
    public function scopeFastResponse($query, float $threshold = 1.0)
    {
        return $query->where('average_response_time', '<=', $threshold);
    }

    /**
     * Verifica se il provider è sano
     */
    public function isHealthy(): bool
    {
        return $this->health_status === 'healthy';
    }

    /**
     * Verifica se il provider è non sano
     */
    public function isUnhealthy(): bool
    {
        return $this->health_status === 'unhealthy';
    }

    /**
     * Verifica se il provider è abilitato
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Verifica se il provider è disabilitato
     */
    public function isDisabled(): bool
    {
        return !$this->enabled;
    }

    /**
     * Ottiene il tasso di successo
     */
    public function getSuccessRateAttribute(): float
    {
        $total = $this->success_count + $this->failure_count;
        return $total > 0 ? round(($this->success_count / $total) * 100, 2) : 0;
    }

    /**
     * Ottiene il tasso di fallimento
     */
    public function getFailureRateAttribute(): float
    {
        $total = $this->success_count + $this->failure_count;
        return $total > 0 ? round(($this->failure_count / $total) * 100, 2) : 0;
    }

    /**
     * Ottiene il tempo di risposta formattato
     */
    public function getFormattedResponseTimeAttribute(): string
    {
        if ($this->average_response_time < 1) {
            return round($this->average_response_time * 1000, 0) . 'ms';
        }
        return round($this->average_response_time, 2) . 's';
    }

    /**
     * Ottiene le statistiche di utilizzo
     */
    public function getUsageStatsAttribute(): array
    {
        return [
            'total_requests' => $this->success_count + $this->failure_count,
            'success_count' => $this->success_count,
            'failure_count' => $this->failure_count,
            'success_rate' => $this->success_rate,
            'failure_rate' => $this->failure_rate,
            'average_response_time' => $this->average_response_time,
            'formatted_response_time' => $this->formatted_response_time
        ];
    }

    /**
     * Ottiene le informazioni di salute
     */
    public function getHealthInfoAttribute(): array
    {
        return [
            'status' => $this->health_status,
            'last_check' => $this->last_health_check,
            'is_healthy' => $this->isHealthy(),
            'is_enabled' => $this->isEnabled(),
            'priority' => $this->priority,
            'timeout' => $this->timeout,
            'retry_attempts' => $this->retry_attempts
        ];
    }

    /**
     * Aggiorna le statistiche di successo
     */
    public function recordSuccess(float $responseTime = null): void
    {
        $this->increment('success_count');
        
        if ($responseTime !== null) {
            $this->updateAverageResponseTime($responseTime);
        }
        
        $this->updateHealthStatus();
    }

    /**
     * Aggiorna le statistiche di fallimento
     */
    public function recordFailure(): void
    {
        $this->increment('failure_count');
        $this->updateHealthStatus();
    }

    /**
     * Aggiorna il tempo di risposta medio
     */
    public function updateAverageResponseTime(float $responseTime): void
    {
        $totalRequests = $this->success_count + $this->failure_count;
        
        if ($totalRequests > 0) {
            $currentAverage = $this->average_response_time ?? 0;
            $newAverage = (($currentAverage * ($totalRequests - 1)) + $responseTime) / $totalRequests;
            $this->update(['average_response_time' => $newAverage]);
        } else {
            $this->update(['average_response_time' => $responseTime]);
        }
    }

    /**
     * Aggiorna lo stato di salute
     */
    public function updateHealthStatus(): void
    {
        $totalRequests = $this->success_count + $this->failure_count;
        
        if ($totalRequests === 0) {
            $this->update(['health_status' => 'unknown']);
            return;
        }

        $successRate = $this->success_count / $totalRequests;
        
        if ($successRate >= 0.9) {
            $this->update(['health_status' => 'healthy']);
        } elseif ($successRate >= 0.7) {
            $this->update(['health_status' => 'warning']);
        } else {
            $this->update(['health_status' => 'unhealthy']);
        }
    }

    /**
     * Aggiorna l'ultimo controllo di salute
     */
    public function updateLastHealthCheck(): void
    {
        $this->update(['last_health_check' => now()]);
    }

    /**
     * Reset delle statistiche
     */
    public function resetStatistics(): void
    {
        $this->update([
            'success_count' => 0,
            'failure_count' => 0,
            'average_response_time' => null,
            'health_status' => 'unknown'
        ]);
    }

    /**
     * Abilita il provider
     */
    public function enable(): void
    {
        $this->update(['enabled' => true]);
    }

    /**
     * Disabilita il provider
     */
    public function disable(): void
    {
        $this->update(['enabled' => false]);
    }

    /**
     * Ottiene le statistiche aggregate per tutti i provider
     */
    public static function getAggregateStats(): array
    {
        $stats = static::selectRaw('
            COUNT(*) as total_providers,
            COUNT(CASE WHEN enabled = 1 THEN 1 END) as enabled_providers,
            COUNT(CASE WHEN enabled = 0 THEN 1 END) as disabled_providers,
            COUNT(CASE WHEN health_status = "healthy" THEN 1 END) as healthy_providers,
            COUNT(CASE WHEN health_status = "unhealthy" THEN 1 END) as unhealthy_providers,
            SUM(success_count) as total_successes,
            SUM(failure_count) as total_failures,
            AVG(average_response_time) as avg_response_time
        ')->first();

        return [
            'total_providers' => $stats->total_providers,
            'enabled_providers' => $stats->enabled_providers,
            'disabled_providers' => $stats->disabled_providers,
            'healthy_providers' => $stats->healthy_providers,
            'unhealthy_providers' => $stats->unhealthy_providers,
            'total_successes' => $stats->total_successes,
            'total_failures' => $stats->total_failures,
            'total_requests' => $stats->total_successes + $stats->total_failures,
            'overall_success_rate' => ($stats->total_successes + $stats->total_failures) > 0 ? 
                round(($stats->total_successes / ($stats->total_successes + $stats->total_failures)) * 100, 2) : 0,
            'avg_response_time' => round($stats->avg_response_time, 3)
        ];
    }

    /**
     * Ottiene le statistiche per priorità
     */
    public static function getStatsByPriority(): array
    {
        return static::select('priority')
            ->selectRaw('COUNT(*) as provider_count')
            ->selectRaw('COUNT(CASE WHEN enabled = 1 THEN 1 END) as enabled_count')
            ->selectRaw('COUNT(CASE WHEN health_status = "healthy" THEN 1 END) as healthy_count')
            ->selectRaw('SUM(success_count) as total_successes')
            ->selectRaw('SUM(failure_count) as total_failures')
            ->selectRaw('AVG(average_response_time) as avg_response_time')
            ->groupBy('priority')
            ->orderBy('priority')
            ->get()
            ->map(function($stat) {
                $total = $stat->total_successes + $stat->total_failures;
                return [
                    'priority' => $stat->priority,
                    'provider_count' => $stat->provider_count,
                    'enabled_count' => $stat->enabled_count,
                    'healthy_count' => $stat->healthy_count,
                    'total_successes' => $stat->total_successes,
                    'total_failures' => $stat->total_failures,
                    'total_requests' => $total,
                    'success_rate' => $total > 0 ? round(($stat->total_successes / $total) * 100, 2) : 0,
                    'avg_response_time' => round($stat->avg_response_time, 3)
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene i provider più performanti
     */
    public static function getTopPerformers(int $limit = 5): array
    {
        return static::enabled()
            ->where('success_count', '>', 0)
            ->orderByRaw('(success_count / (success_count + failure_count)) DESC')
            ->orderBy('average_response_time', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($provider) {
                return [
                    'name' => $provider->name,
                    'display_name' => $provider->display_name,
                    'success_rate' => $provider->success_rate,
                    'avg_response_time' => $provider->average_response_time,
                    'total_requests' => $provider->success_count + $provider->failure_count
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene i provider problematici
     */
    public static function getProblematicProviders(int $limit = 5): array
    {
        return static::enabled()
            ->where('failure_count', '>', 0)
            ->orderByRaw('(failure_count / (success_count + failure_count)) DESC')
            ->orderBy('failure_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($provider) {
                return [
                    'name' => $provider->name,
                    'display_name' => $provider->display_name,
                    'failure_rate' => $provider->failure_rate,
                    'failure_count' => $provider->failure_count,
                    'health_status' => $provider->health_status
                ];
            })
            ->toArray();
    }

    /**
     * Pulisce i provider inutilizzati
     */
    public static function cleanupUnusedProviders(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);
        return static::where('last_health_check', '<', $cutoffDate)
            ->where('success_count', 0)
            ->where('failure_count', 0)
            ->delete();
    }
}
