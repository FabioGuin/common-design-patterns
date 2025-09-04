<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AIRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'provider',
        'prompt',
        'response',
        'success',
        'duration',
        'cost',
        'tokens_used',
        'error_message'
    ];

    protected $casts = [
        'response' => 'array',
        'success' => 'boolean',
        'duration' => 'float',
        'cost' => 'float',
        'tokens_used' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
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
     * Scope per provider specifico
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope per periodo di tempo
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope per costo minimo
     */
    public function scopeWithMinCost($query, float $minCost)
    {
        return $query->where('cost', '>=', $minCost);
    }

    /**
     * Scope per durata massima
     */
    public function scopeWithMaxDuration($query, float $maxDuration)
    {
        return $query->where('duration', '<=', $maxDuration);
    }

    /**
     * Statistiche per provider
     */
    public static function getProviderStats(string $provider, $startDate = null, $endDate = null)
    {
        $query = static::byProvider($provider);
        
        if ($startDate && $endDate) {
            $query->inPeriod($startDate, $endDate);
        }
        
        return [
            'total_requests' => $query->count(),
            'successful_requests' => $query->successful()->count(),
            'failed_requests' => $query->failed()->count(),
            'success_rate' => $query->count() > 0 ? round($query->successful()->count() / $query->count() * 100, 2) : 0,
            'total_cost' => $query->sum('cost'),
            'average_cost' => $query->avg('cost'),
            'total_tokens' => $query->sum('tokens_used'),
            'average_tokens' => $query->avg('tokens_used'),
            'average_duration' => $query->avg('duration'),
            'min_duration' => $query->min('duration'),
            'max_duration' => $query->max('duration')
        ];
    }

    /**
     * Statistiche globali
     */
    public static function getGlobalStats($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->inPeriod($startDate, $endDate);
        }
        
        $totalRequests = $query->count();
        $successfulRequests = $query->successful()->count();
        
        return [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'failed_requests' => $totalRequests - $successfulRequests,
            'success_rate' => $totalRequests > 0 ? round($successfulRequests / $totalRequests * 100, 2) : 0,
            'total_cost' => $query->sum('cost'),
            'average_cost' => $query->avg('cost'),
            'total_tokens' => $query->sum('tokens_used'),
            'average_tokens' => $query->avg('tokens_used'),
            'average_duration' => $query->avg('duration'),
            'providers_used' => $query->distinct('provider')->pluck('provider')->toArray(),
            'requests_by_provider' => $query->selectRaw('provider, COUNT(*) as count')
                ->groupBy('provider')
                ->pluck('count', 'provider')
                ->toArray()
        ];
    }

    /**
     * Top prompt più utilizzati
     */
    public static function getTopPrompts(int $limit = 10, $startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->inPeriod($startDate, $endDate);
        }
        
        return $query->selectRaw('prompt, COUNT(*) as count')
            ->groupBy('prompt')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'prompt' => $item->prompt,
                    'count' => $item->count,
                    'preview' => substr($item->prompt, 0, 100) . '...'
                ];
            });
    }

    /**
     * Costi per giorno
     */
    public static function getDailyCosts($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->inPeriod($startDate, $endDate);
        }
        
        return $query->selectRaw('DATE(created_at) as date, SUM(cost) as total_cost, COUNT(*) as requests')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total_cost' => round($item->total_cost, 4),
                    'requests' => $item->requests,
                    'average_cost' => round($item->total_cost / $item->requests, 4)
                ];
            });
    }

    /**
     * Performance per provider
     */
    public static function getProviderPerformance($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->inPeriod($startDate, $endDate);
        }
        
        return $query->selectRaw('
                provider,
                COUNT(*) as total_requests,
                AVG(duration) as avg_duration,
                AVG(cost) as avg_cost,
                SUM(cost) as total_cost,
                AVG(tokens_used) as avg_tokens,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_requests
            ')
            ->groupBy('provider')
            ->get()
            ->map(function ($item) {
                return [
                    'provider' => $item->provider,
                    'total_requests' => $item->total_requests,
                    'successful_requests' => $item->successful_requests,
                    'success_rate' => round($item->successful_requests / $item->total_requests * 100, 2),
                    'avg_duration' => round($item->avg_duration, 3),
                    'avg_cost' => round($item->avg_cost, 4),
                    'total_cost' => round($item->total_cost, 4),
                    'avg_tokens' => round($item->avg_tokens, 0)
                ];
            });
    }
}
