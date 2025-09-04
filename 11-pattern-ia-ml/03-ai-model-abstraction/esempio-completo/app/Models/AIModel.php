<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'provider',
        'description',
        'capabilities',
        'cost_per_token',
        'max_tokens',
        'context_window',
        'priority',
        'enabled',
        'tags',
        'metadata'
    ];

    protected $casts = [
        'capabilities' => 'array',
        'tags' => 'array',
        'metadata' => 'array',
        'enabled' => 'boolean',
        'cost_per_token' => 'float',
        'max_tokens' => 'integer',
        'context_window' => 'integer',
        'priority' => 'integer'
    ];

    /**
     * Scope per modelli abilitati
     */
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope per provider specifico
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope per capacità specifica
     */
    public function scopeByCapability($query, string $capability)
    {
        return $query->whereJsonContains('capabilities', $capability);
    }

    /**
     * Scope per priorità
     */
    public function scopeByPriority($query, int $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope per range di costo
     */
    public function scopeByCostRange($query, float $minCost, float $maxCost)
    {
        return $query->whereBetween('cost_per_token', [$minCost, $maxCost]);
    }

    /**
     * Scope per tag specifico
     */
    public function scopeByTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Verifica se il modello ha una capacità specifica
     */
    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? []);
    }

    /**
     * Verifica se il modello ha un tag specifico
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    /**
     * Ottiene le capacità come stringa
     */
    public function getCapabilitiesStringAttribute(): string
    {
        return implode(', ', $this->capabilities ?? []);
    }

    /**
     * Ottiene i tag come stringa
     */
    public function getTagsStringAttribute(): string
    {
        return implode(', ', $this->tags ?? []);
    }

    /**
     * Ottiene il costo formattato
     */
    public function getFormattedCostAttribute(): string
    {
        return '$' . number_format($this->cost_per_token, 6);
    }

    /**
     * Ottiene le statistiche di utilizzo
     */
    public function usageStats()
    {
        return $this->hasMany(ModelUsage::class, 'model_name', 'name');
    }

    /**
     * Ottiene le statistiche di performance
     */
    public function performanceStats()
    {
        return $this->hasOne(ModelPerformance::class, 'model_name', 'name');
    }

    /**
     * Ottiene le statistiche aggregate
     */
    public function getAggregateStatsAttribute(): array
    {
        $usage = $this->usageStats();
        $totalRequests = $usage->count();
        $successfulRequests = $usage->where('success', true)->count();
        $totalCost = $usage->sum('cost');
        $averageDuration = $usage->avg('duration');

        return [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'success_rate' => $totalRequests > 0 ? round(($successfulRequests / $totalRequests) * 100, 2) : 0,
            'total_cost' => round($totalCost, 4),
            'average_cost' => $totalRequests > 0 ? round($totalCost / $totalRequests, 4) : 0,
            'average_duration' => round($averageDuration, 2)
        ];
    }

    /**
     * Ottiene le statistiche per periodo
     */
    public function getStatsForPeriod(int $days = 7): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $usage = $this->usageStats()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

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
    public static function getMostUsed(int $limit = 10): array
    {
        return static::withCount('usageStats')
            ->orderBy('usage_stats_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($model) {
                return [
                    'model' => $model,
                    'usage_count' => $model->usage_stats_count,
                    'stats' => $model->aggregate_stats
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene i modelli per provider
     */
    public static function getByProvider(string $provider): \Illuminate\Database\Eloquent\Collection
    {
        return static::byProvider($provider)->enabled()->get();
    }

    /**
     * Ottiene i modelli per capacità
     */
    public static function getByCapability(string $capability): \Illuminate\Database\Eloquent\Collection
    {
        return static::byCapability($capability)->enabled()->get();
    }

    /**
     * Ottiene i modelli ordinati per priorità
     */
    public static function getByPriority(): \Illuminate\Database\Eloquent\Collection
    {
        return static::enabled()->orderBy('priority')->get();
    }

    /**
     * Ottiene i modelli ordinati per costo
     */
    public static function getByCost(): \Illuminate\Database\Eloquent\Collection
    {
        return static::enabled()->orderBy('cost_per_token')->get();
    }

    /**
     * Cerca modelli per nome o descrizione
     */
    public static function search(string $query): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->enabled()
            ->get();
    }

    /**
     * Ottiene le statistiche aggregate per provider
     */
    public static function getProviderStats(): array
    {
        $providers = static::select('provider')
            ->selectRaw('COUNT(*) as model_count')
            ->selectRaw('AVG(cost_per_token) as avg_cost')
            ->selectRaw('AVG(priority) as avg_priority')
            ->groupBy('provider')
            ->get();

        return $providers->map(function($provider) {
            return [
                'provider' => $provider->provider,
                'model_count' => $provider->model_count,
                'avg_cost' => round($provider->avg_cost, 6),
                'avg_priority' => round($provider->avg_priority, 2)
            ];
        })->toArray();
    }

    /**
     * Ottiene le statistiche aggregate per capacità
     */
    public static function getCapabilityStats(): array
    {
        $capabilities = static::select('capabilities')
            ->get()
            ->pluck('capabilities')
            ->flatten()
            ->countBy();

        return $capabilities->map(function($count, $capability) {
            return [
                'capability' => $capability,
                'model_count' => $count
            ];
        })->values()->toArray();
    }

    /**
     * Sincronizza i modelli dalla configurazione
     */
    public static function syncFromConfig(): void
    {
        $models = config('ai_models.models', []);

        foreach ($models as $name => $config) {
            static::updateOrCreate(
                ['name' => $name],
                [
                    'provider' => $config['provider'],
                    'description' => $config['description'] ?? '',
                    'capabilities' => $config['capabilities'] ?? [],
                    'cost_per_token' => $config['cost_per_token'] ?? 0,
                    'max_tokens' => $config['max_tokens'] ?? 4096,
                    'context_window' => $config['context_window'] ?? 4096,
                    'priority' => $config['priority'] ?? 5,
                    'enabled' => $config['enabled'] ?? true,
                    'tags' => $config['tags'] ?? [],
                    'metadata' => $config
                ]
            );
        }
    }

    /**
     * Ottiene i modelli consigliati per un task
     */
    public static function getRecommendedForTask(string $task, array $constraints = []): \Illuminate\Database\Eloquent\Collection
    {
        $taskMapping = config('ai_models.task_mapping.' . $task, []);
        $preferredModels = $taskMapping['preferred_models'] ?? [];
        $fallbackModels = $taskMapping['fallback_models'] ?? [];
        
        $allModels = array_merge($preferredModels, $fallbackModels);
        
        $query = static::whereIn('name', $allModels)->enabled();
        
        // Applica vincoli
        if (isset($constraints['max_cost'])) {
            $query->where('cost_per_token', '<=', $constraints['max_cost']);
        }
        
        if (isset($constraints['required_capabilities'])) {
            foreach ($constraints['required_capabilities'] as $capability) {
                $query->whereJsonContains('capabilities', $capability);
            }
        }
        
        if (isset($constraints['provider'])) {
            $query->where('provider', $constraints['provider']);
        }
        
        return $query->orderBy('priority')->get();
    }
}
