<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromptTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'template',
        'variables',
        'validation_rules',
        'description',
        'is_custom',
        'result',
        'validation_result',
        'success',
        'quality_score',
        'cost',
        'duration'
    ];

    protected $casts = [
        'variables' => 'array',
        'validation_rules' => 'array',
        'result' => 'array',
        'validation_result' => 'array',
        'is_custom' => 'boolean',
        'success' => 'boolean',
        'quality_score' => 'float',
        'cost' => 'float',
        'duration' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Scope per template personalizzati
     */
    public function scopeCustom($query)
    {
        return $query->where('is_custom', true);
    }

    /**
     * Scope per template di sistema
     */
    public function scopeSystem($query)
    {
        return $query->where('is_custom', false);
    }

    /**
     * Scope per template di successo
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope per template con qualità minima
     */
    public function scopeWithMinQuality($query, float $minQuality)
    {
        return $query->where('quality_score', '>=', $minQuality);
    }

    /**
     * Scope per template utilizzati di recente
     */
    public function scopeRecentlyUsed($query, int $days = 7)
    {
        return $query->where('updated_at', '>=', now()->subDays($days));
    }

    /**
     * Ottiene le statistiche per template
     */
    public static function getTemplateStats(string $templateName, $startDate = null, $endDate = null)
    {
        $query = static::where('name', $templateName);
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return [
            'total_uses' => $query->count(),
            'successful_uses' => $query->successful()->count(),
            'success_rate' => $query->count() > 0 ? round($query->successful()->count() / $query->count() * 100, 2) : 0,
            'average_quality' => $query->avg('quality_score'),
            'average_cost' => $query->avg('cost'),
            'average_duration' => $query->avg('duration'),
            'total_cost' => $query->sum('cost'),
            'best_quality' => $query->max('quality_score'),
            'worst_quality' => $query->min('quality_score')
        ];
    }

    /**
     * Ottiene le statistiche globali
     */
    public static function getGlobalStats($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $totalUses = $query->count();
        $successfulUses = $query->successful()->count();
        
        return [
            'total_uses' => $totalUses,
            'successful_uses' => $successfulUses,
            'success_rate' => $totalUses > 0 ? round($successfulUses / $totalUses * 100, 2) : 0,
            'average_quality' => $query->avg('quality_score'),
            'average_cost' => $query->avg('cost'),
            'average_duration' => $query->avg('duration'),
            'total_cost' => $query->sum('cost'),
            'templates_used' => $query->distinct('name')->pluck('name')->toArray(),
            'custom_templates' => $query->custom()->count(),
            'system_templates' => $query->system()->count()
        ];
    }

    /**
     * Ottiene i template più utilizzati
     */
    public static function getMostUsedTemplates(int $limit = 10, $startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->selectRaw('name, COUNT(*) as usage_count, AVG(quality_score) as avg_quality, AVG(cost) as avg_cost')
            ->groupBy('name')
            ->orderBy('usage_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'usage_count' => $item->usage_count,
                    'avg_quality' => round($item->avg_quality, 2),
                    'avg_cost' => round($item->avg_cost, 4)
                ];
            });
    }

    /**
     * Ottiene i template con migliore qualità
     */
    public static function getBestQualityTemplates(int $limit = 10, $startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->selectRaw('name, AVG(quality_score) as avg_quality, COUNT(*) as usage_count, AVG(cost) as avg_cost')
            ->groupBy('name')
            ->having('usage_count', '>=', 5) // Almeno 5 utilizzi
            ->orderBy('avg_quality', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'avg_quality' => round($item->avg_quality, 2),
                    'usage_count' => $item->usage_count,
                    'avg_cost' => round($item->avg_cost, 4)
                ];
            });
    }

    /**
     * Ottiene i template più costosi
     */
    public static function getMostExpensiveTemplates(int $limit = 10, $startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->selectRaw('name, AVG(cost) as avg_cost, COUNT(*) as usage_count, AVG(quality_score) as avg_quality')
            ->groupBy('name')
            ->having('usage_count', '>=', 3) // Almeno 3 utilizzi
            ->orderBy('avg_cost', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'avg_cost' => round($item->avg_cost, 4),
                    'usage_count' => $item->usage_count,
                    'avg_quality' => round($item->avg_quality, 2)
                ];
            });
    }

    /**
     * Ottiene l'evoluzione della qualità nel tempo
     */
    public static function getQualityEvolution(string $templateName, $startDate = null, $endDate = null)
    {
        $query = static::where('name', $templateName);
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->selectRaw('DATE(created_at) as date, AVG(quality_score) as avg_quality, COUNT(*) as usage_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'avg_quality' => round($item->avg_quality, 2),
                    'usage_count' => $item->usage_count
                ];
            });
    }

    /**
     * Ottiene le variabili più utilizzate
     */
    public static function getMostUsedVariables($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $templates = $query->get();
        $variableCounts = [];
        
        foreach ($templates as $template) {
            $variables = $template->variables ?? [];
            foreach ($variables as $variable) {
                $variableCounts[$variable] = ($variableCounts[$variable] ?? 0) + 1;
            }
        }
        
        arsort($variableCounts);
        
        return array_map(function ($variable, $count) {
            return [
                'variable' => $variable,
                'usage_count' => $count
            ];
        }, array_keys($variableCounts), array_values($variableCounts));
    }

    /**
     * Ottiene le performance per giorno della settimana
     */
    public static function getPerformanceByDayOfWeek($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->selectRaw('DAYOFWEEK(created_at) as day_of_week, AVG(quality_score) as avg_quality, AVG(cost) as avg_cost, COUNT(*) as usage_count')
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get()
            ->map(function ($item) {
                $days = ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'];
                
                return [
                    'day' => $days[$item->day_of_week - 1] ?? 'Sconosciuto',
                    'day_of_week' => $item->day_of_week,
                    'avg_quality' => round($item->avg_quality, 2),
                    'avg_cost' => round($item->avg_cost, 4),
                    'usage_count' => $item->usage_count
                ];
            });
    }
}
