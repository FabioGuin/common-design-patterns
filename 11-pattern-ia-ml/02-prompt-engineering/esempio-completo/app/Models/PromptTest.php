<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromptTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'template_name',
        'variables',
        'results',
        'analysis',
        'success_rate',
        'average_quality',
        'total_cost',
        'average_duration',
        'iterations'
    ];

    protected $casts = [
        'variables' => 'array',
        'results' => 'array',
        'analysis' => 'array',
        'success_rate' => 'float',
        'average_quality' => 'float',
        'total_cost' => 'float',
        'average_duration' => 'float',
        'iterations' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Scope per test di successo
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success_rate', '>=', 80);
    }

    /**
     * Scope per test con qualità minima
     */
    public function scopeWithMinQuality($query, float $minQuality)
    {
        return $query->where('average_quality', '>=', $minQuality);
    }

    /**
     * Scope per test recenti
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope per template specifico
     */
    public function scopeForTemplate($query, string $templateName)
    {
        return $query->where('template_name', $templateName);
    }

    /**
     * Ottiene le statistiche per template
     */
    public static function getTemplateTestStats(string $templateName, $startDate = null, $endDate = null)
    {
        $query = static::forTemplate($templateName);
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return [
            'total_tests' => $query->count(),
            'successful_tests' => $query->successful()->count(),
            'average_success_rate' => $query->avg('success_rate'),
            'average_quality' => $query->avg('average_quality'),
            'average_cost' => $query->avg('total_cost'),
            'average_duration' => $query->avg('average_duration'),
            'total_iterations' => $query->sum('iterations'),
            'best_quality' => $query->max('average_quality'),
            'worst_quality' => $query->min('average_quality')
        ];
    }

    /**
     * Ottiene le statistiche globali dei test
     */
    public static function getGlobalTestStats($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return [
            'total_tests' => $query->count(),
            'successful_tests' => $query->successful()->count(),
            'average_success_rate' => $query->avg('success_rate'),
            'average_quality' => $query->avg('average_quality'),
            'average_cost' => $query->avg('total_cost'),
            'average_duration' => $query->avg('average_duration'),
            'total_iterations' => $query->sum('iterations'),
            'templates_tested' => $query->distinct('template_name')->pluck('template_name')->toArray()
        ];
    }

    /**
     * Ottiene i template più testati
     */
    public static function getMostTestedTemplates(int $limit = 10, $startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->selectRaw('template_name, COUNT(*) as test_count, AVG(success_rate) as avg_success_rate, AVG(average_quality) as avg_quality')
            ->groupBy('template_name')
            ->orderBy('test_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'template_name' => $item->template_name,
                    'test_count' => $item->test_count,
                    'avg_success_rate' => round($item->avg_success_rate, 2),
                    'avg_quality' => round($item->avg_quality, 2)
                ];
            });
    }

    /**
     * Ottiene i test con migliore performance
     */
    public static function getBestPerformingTests(int $limit = 10, $startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->selectRaw('test_id, template_name, success_rate, average_quality, total_cost, average_duration, iterations')
            ->where('iterations', '>=', 5) // Almeno 5 iterazioni
            ->orderBy('average_quality', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'test_id' => $item->test_id,
                    'template_name' => $item->template_name,
                    'success_rate' => round($item->success_rate, 2),
                    'average_quality' => round($item->average_quality, 2),
                    'total_cost' => round($item->total_cost, 4),
                    'average_duration' => round($item->average_duration, 2),
                    'iterations' => $item->iterations
                ];
            });
    }

    /**
     * Ottiene l'evoluzione della qualità dei test nel tempo
     */
    public static function getTestQualityEvolution($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->selectRaw('DATE(created_at) as date, AVG(average_quality) as avg_quality, AVG(success_rate) as avg_success_rate, COUNT(*) as test_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'avg_quality' => round($item->avg_quality, 2),
                    'avg_success_rate' => round($item->avg_success_rate, 2),
                    'test_count' => $item->test_count
                ];
            });
    }

    /**
     * Ottiene le performance per numero di iterazioni
     */
    public static function getPerformanceByIterations($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->selectRaw('iterations, AVG(average_quality) as avg_quality, AVG(success_rate) as avg_success_rate, AVG(total_cost) as avg_cost, COUNT(*) as test_count')
            ->groupBy('iterations')
            ->orderBy('iterations')
            ->get()
            ->map(function ($item) {
                return [
                    'iterations' => $item->iterations,
                    'avg_quality' => round($item->avg_quality, 2),
                    'avg_success_rate' => round($item->avg_success_rate, 2),
                    'avg_cost' => round($item->avg_cost, 4),
                    'test_count' => $item->test_count
                ];
            });
    }

    /**
     * Ottiene i test con costi più alti
     */
    public static function getMostExpensiveTests(int $limit = 10, $startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->selectRaw('test_id, template_name, total_cost, average_quality, success_rate, iterations')
            ->orderBy('total_cost', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'test_id' => $item->test_id,
                    'template_name' => $item->template_name,
                    'total_cost' => round($item->total_cost, 4),
                    'average_quality' => round($item->average_quality, 2),
                    'success_rate' => round($item->success_rate, 2),
                    'iterations' => $item->iterations
                ];
            });
    }

    /**
     * Ottiene i test con durata più lunga
     */
    public static function getLongestTests(int $limit = 10, $startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->selectRaw('test_id, template_name, average_duration, average_quality, success_rate, iterations')
            ->orderBy('average_duration', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'test_id' => $item->test_id,
                    'template_name' => $item->template_name,
                    'average_duration' => round($item->average_duration, 2),
                    'average_quality' => round($item->average_quality, 2),
                    'success_rate' => round($item->success_rate, 2),
                    'iterations' => $item->iterations
                ];
            });
    }

    /**
     * Ottiene le correlazioni tra variabili
     */
    public static function getVariableCorrelations($startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $tests = $query->get();
        $correlations = [];
        
        foreach ($tests as $test) {
            $variables = $test->variables ?? [];
            $quality = $test->average_quality;
            
            foreach ($variables as $key => $value) {
                if (!isset($correlations[$key])) {
                    $correlations[$key] = [];
                }
                $correlations[$key][] = [
                    'value' => $value,
                    'quality' => $quality
                ];
            }
        }
        
        // Calcola correlazioni semplificate
        $results = [];
        foreach ($correlations as $variable => $data) {
            if (count($data) < 3) continue;
            
            $values = array_column($data, 'value');
            $qualities = array_column($data, 'quality');
            
            // Calcola correlazione semplificata
            $correlation = $this->calculateSimpleCorrelation($values, $qualities);
            
            $results[] = [
                'variable' => $variable,
                'correlation' => round($correlation, 3),
                'sample_size' => count($data)
            ];
        }
        
        usort($results, fn($a, $b) => abs($b['correlation']) <=> abs($a['correlation']));
        
        return $results;
    }

    /**
     * Calcola correlazione semplificata
     */
    private static function calculateSimpleCorrelation(array $x, array $y): float
    {
        if (count($x) !== count($y) || count($x) < 2) {
            return 0;
        }
        
        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
            $sumY2 += $y[$i] * $y[$i];
        }
        
        $numerator = $n * $sumXY - $sumX * $sumY;
        $denominator = sqrt(($n * $sumX2 - $sumX * $sumX) * ($n * $sumY2 - $sumY * $sumY));
        
        return $denominator == 0 ? 0 : $numerator / $denominator;
    }
}
