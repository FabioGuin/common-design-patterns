<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\AIModel;
use App\Models\ModelUsage;
use App\Models\ModelPerformance;

class AIModelService
{
    private AIModelRegistry $registry;
    private AIModelSelector $selector;
    private ModelPerformanceTracker $performanceTracker;
    private array $config;

    public function __construct(
        AIModelRegistry $registry,
        AIModelSelector $selector,
        ModelPerformanceTracker $performanceTracker
    ) {
        $this->registry = $registry;
        $this->selector = $selector;
        $this->performanceTracker = $performanceTracker;
        $this->config = config('ai_models', []);
    }

    /**
     * Genera testo usando il modello migliore
     */
    public function generateText(string $prompt, array $options = []): array
    {
        $requestId = uniqid();
        $startTime = microtime(true);

        Log::info('AI Model Text Generation Started', [
            'request_id' => $requestId,
            'prompt_length' => strlen($prompt),
            'options' => $options
        ]);

        try {
            // Seleziona il modello migliore
            $model = $this->selector->selectBestModel('text_generation', $options);
            
            if (!$model) {
                throw new \Exception('Nessun modello disponibile per la generazione di testo');
            }

            // Genera il testo
            $result = $model->generateText($prompt, $options);
            $duration = microtime(true) - $startTime;

            // Calcola il costo
            $cost = $this->calculateCost($model, $result);

            $response = [
                'text' => $result['text'],
                'model' => $model->getName(),
                'provider' => $model->getProvider(),
                'duration' => $duration,
                'cost' => $cost,
                'tokens_used' => $result['tokens_used'] ?? 0,
                'request_id' => $requestId
            ];

            // Traccia le performance
            $this->performanceTracker->trackUsage($model, $response, true);

            // Salva nel database
            $this->saveUsage($requestId, $model, $prompt, $response, true);

            Log::info('AI Model Text Generation Completed', [
                'request_id' => $requestId,
                'model' => $model->getName(),
                'duration' => $duration,
                'cost' => $cost
            ]);

            return $response;

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            
            Log::error('AI Model Text Generation Failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'duration' => $duration
            ]);

            // Traccia l'errore
            $this->performanceTracker->trackError($e->getMessage(), $duration);

            // Salva errore nel database
            $this->saveUsage($requestId, null, $prompt, ['error' => $e->getMessage()], false);

            throw $e;
        }
    }

    /**
     * Genera immagine usando il modello specifico
     */
    public function generateImage(string $prompt, array $options = []): array
    {
        $requestId = uniqid();
        $startTime = microtime(true);

        Log::info('AI Model Image Generation Started', [
            'request_id' => $requestId,
            'prompt_length' => strlen($prompt)
        ]);

        try {
            // Seleziona il modello per immagini
            $model = $this->selector->selectBestModel('image_generation', $options);
            
            if (!$model) {
                throw new \Exception('Nessun modello disponibile per la generazione di immagini');
            }

            // Genera l'immagine
            $result = $model->generateImage($prompt, $options);
            $duration = microtime(true) - $startTime;

            $response = [
                'image_url' => $result['image_url'],
                'model' => $model->getName(),
                'provider' => $model->getProvider(),
                'duration' => $duration,
                'cost' => $result['cost'] ?? 0,
                'request_id' => $requestId
            ];

            // Traccia le performance
            $this->performanceTracker->trackUsage($model, $response, true);

            // Salva nel database
            $this->saveUsage($requestId, $model, $prompt, $response, true);

            Log::info('AI Model Image Generation Completed', [
                'request_id' => $requestId,
                'model' => $model->getName(),
                'duration' => $duration
            ]);

            return $response;

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            
            Log::error('AI Model Image Generation Failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'duration' => $duration
            ]);

            throw $e;
        }
    }

    /**
     * Traduce testo usando il modello più economico
     */
    public function translate(string $text, string $targetLanguage, array $options = []): array
    {
        $options['task'] = 'translation';
        $options['optimize_for'] = 'cost';
        
        $prompt = "Traduci il seguente testo in {$targetLanguage}: {$text}";
        
        return $this->generateText($prompt, $options);
    }

    /**
     * Analizza contenuto usando il modello più adatto
     */
    public function analyzeContent(string $content, string $analysisType, array $options = []): array
    {
        $options['task'] = 'analysis';
        $options['analysis_type'] = $analysisType;
        
        $prompt = "Analizza il seguente contenuto per {$analysisType}: {$content}";
        
        return $this->generateText($prompt, $options);
    }

    /**
     * Testa un modello specifico
     */
    public function testModel(string $modelName, array $options = []): array
    {
        $model = $this->registry->getModel($modelName);
        
        if (!$model) {
            throw new \Exception("Modello '{$modelName}' non trovato");
        }

        $testPrompt = $options['prompt'] ?? 'Test prompt per verificare il funzionamento del modello';
        $iterations = $options['iterations'] ?? 5;
        $results = [];

        Log::info('AI Model Test Started', [
            'model' => $modelName,
            'iterations' => $iterations
        ]);

        for ($i = 0; $i < $iterations; $i++) {
            try {
                $startTime = microtime(true);
                $result = $model->generateText($testPrompt, $options);
                $duration = microtime(true) - $startTime;

                $results[] = [
                    'iteration' => $i + 1,
                    'success' => true,
                    'duration' => $duration,
                    'cost' => $this->calculateCost($model, $result),
                    'tokens_used' => $result['tokens_used'] ?? 0
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'iteration' => $i + 1,
                    'success' => false,
                    'error' => $e->getMessage(),
                    'duration' => 0,
                    'cost' => 0
                ];
            }
        }

        // Analizza i risultati
        $analysis = $this->analyzeTestResults($results);

        return [
            'model' => $modelName,
            'iterations' => $iterations,
            'results' => $results,
            'analysis' => $analysis
        ];
    }

    /**
     * Confronta modelli per un task specifico
     */
    public function compareModels(array $modelNames, array $options = []): array
    {
        $testPrompt = $options['test_prompt'] ?? 'Test prompt per confronto modelli';
        $iterations = $options['iterations'] ?? 3;
        $task = $options['task'] ?? 'text_generation';

        $results = [];

        foreach ($modelNames as $modelName) {
            try {
                $model = $this->registry->getModel($modelName);
                if (!$model) {
                    continue;
                }

                $modelResults = [];
                for ($i = 0; $i < $iterations; $i++) {
                    $startTime = microtime(true);
                    $result = $model->generateText($testPrompt, $options);
                    $duration = microtime(true) - $startTime;

                    $modelResults[] = [
                        'duration' => $duration,
                        'cost' => $this->calculateCost($model, $result),
                        'tokens_used' => $result['tokens_used'] ?? 0,
                        'success' => true
                    ];
                }

                $analysis = $this->analyzeTestResults($modelResults);

                $results[$modelName] = [
                    'model' => $modelName,
                    'provider' => $model->getProvider(),
                    'results' => $modelResults,
                    'analysis' => $analysis
                ];

            } catch (\Exception $e) {
                $results[$modelName] = [
                    'model' => $modelName,
                    'error' => $e->getMessage(),
                    'analysis' => ['success_rate' => 0, 'average_duration' => 0, 'average_cost' => 0]
                ];
            }
        }

        // Confronta i risultati
        $comparison = $this->compareModelResults($results);

        return [
            'task' => $task,
            'test_prompt' => $testPrompt,
            'iterations' => $iterations,
            'results' => $results,
            'comparison' => $comparison
        ];
    }

    /**
     * Esegue benchmark su modelli e task
     */
    public function runBenchmark(array $config): array
    {
        $models = $config['models'] ?? [];
        $tasks = $config['tasks'] ?? ['text_generation'];
        $iterations = $config['iterations'] ?? 5;

        $benchmarkResults = [];

        foreach ($models as $modelName) {
            $model = $this->registry->getModel($modelName);
            if (!$model) {
                continue;
            }

            $modelResults = [];

            foreach ($tasks as $task) {
                $taskResults = [];
                
                for ($i = 0; $i < $iterations; $i++) {
                    try {
                        $startTime = microtime(true);
                        $result = $model->generateText("Test prompt for {$task}", ['task' => $task]);
                        $duration = microtime(true) - $startTime;

                        $taskResults[] = [
                            'duration' => $duration,
                            'cost' => $this->calculateCost($model, $result),
                            'success' => true
                        ];

                    } catch (\Exception $e) {
                        $taskResults[] = [
                            'duration' => 0,
                            'cost' => 0,
                            'success' => false,
                            'error' => $e->getMessage()
                        ];
                    }
                }

                $analysis = $this->analyzeTestResults($taskResults);
                $modelResults[$task] = $analysis;
            }

            $benchmarkResults[$modelName] = [
                'model' => $modelName,
                'provider' => $model->getProvider(),
                'tasks' => $modelResults
            ];
        }

        return [
            'benchmark_config' => $config,
            'results' => $benchmarkResults,
            'summary' => $this->generateBenchmarkSummary($benchmarkResults)
        ];
    }

    /**
     * Ottiene le statistiche di performance
     */
    public function getPerformanceStats(string $modelName = null, $startDate = null, $endDate = null): array
    {
        $query = ModelUsage::query();

        if ($modelName) {
            $query->where('model_name', $modelName);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalRequests = $query->count();
        $successfulRequests = $query->where('success', true)->count();
        $totalCost = $query->sum('cost');
        $averageDuration = $query->avg('duration');

        return [
            'total_requests' => $totalRequests,
            'successful_requests' => $successfulRequests,
            'success_rate' => $totalRequests > 0 ? round($successfulRequests / $totalRequests * 100, 2) : 0,
            'total_cost' => round($totalCost, 4),
            'average_cost' => $totalRequests > 0 ? round($totalCost / $totalRequests, 4) : 0,
            'average_duration' => round($averageDuration, 2),
            'models_used' => $query->distinct('model_name')->pluck('model_name')->toArray()
        ];
    }

    /**
     * Calcola il costo di una richiesta
     */
    private function calculateCost($model, array $result): float
    {
        $tokensUsed = $result['tokens_used'] ?? 0;
        $costPerToken = $model->getCostPerToken();
        
        return $tokensUsed * $costPerToken;
    }

    /**
     * Analizza i risultati di un test
     */
    private function analyzeTestResults(array $results): array
    {
        if (empty($results)) {
            return ['error' => 'Nessun risultato da analizzare'];
        }

        $successfulResults = array_filter($results, fn($r) => $r['success'] ?? false);
        $durations = array_column($successfulResults, 'duration');
        $costs = array_column($successfulResults, 'cost');

        return [
            'total_tests' => count($results),
            'successful_tests' => count($successfulResults),
            'success_rate' => count($results) > 0 ? round(count($successfulResults) / count($results) * 100, 2) : 0,
            'average_duration' => !empty($durations) ? round(array_sum($durations) / count($durations), 2) : 0,
            'min_duration' => !empty($durations) ? round(min($durations), 2) : 0,
            'max_duration' => !empty($durations) ? round(max($durations), 2) : 0,
            'average_cost' => !empty($costs) ? round(array_sum($costs) / count($costs), 4) : 0,
            'total_cost' => round(array_sum($costs), 4)
        ];
    }

    /**
     * Confronta i risultati di modelli diversi
     */
    private function compareModelResults(array $results): array
    {
        $comparison = [];
        
        foreach ($results as $modelName => $data) {
            if (isset($data['analysis'])) {
                $comparison[$modelName] = [
                    'success_rate' => $data['analysis']['success_rate'],
                    'average_duration' => $data['analysis']['average_duration'],
                    'average_cost' => $data['analysis']['average_cost'],
                    'total_cost' => $data['analysis']['total_cost']
                ];
            }
        }

        // Trova il migliore per ogni metrica
        $bestSuccessRate = max(array_column($comparison, 'success_rate'));
        $bestDuration = min(array_column($comparison, 'average_duration'));
        $bestCost = min(array_column($comparison, 'average_cost'));

        return [
            'models' => $comparison,
            'best_success_rate' => $bestSuccessRate,
            'best_duration' => $bestDuration,
            'best_cost' => $bestCost,
            'recommendations' => $this->generateRecommendations($comparison)
        ];
    }

    /**
     * Genera raccomandazioni basate sui risultati
     */
    private function generateRecommendations(array $comparison): array
    {
        $recommendations = [];

        // Trova il modello con il miglior success rate
        $bestSuccessModel = array_search(max(array_column($comparison, 'success_rate')), array_column($comparison, 'success_rate'));
        $recommendations[] = "Per affidabilità: {$bestSuccessModel}";

        // Trova il modello più veloce
        $fastestModel = array_search(min(array_column($comparison, 'average_duration')), array_column($comparison, 'average_duration'));
        $recommendations[] = "Per velocità: {$fastestModel}";

        // Trova il modello più economico
        $cheapestModel = array_search(min(array_column($comparison, 'average_cost')), array_column($comparison, 'average_cost'));
        $recommendations[] = "Per costo: {$cheapestModel}";

        return $recommendations;
    }

    /**
     * Genera summary del benchmark
     */
    private function generateBenchmarkSummary(array $results): array
    {
        $summary = [];

        foreach ($results as $modelName => $data) {
            $summary[$modelName] = [
                'overall_success_rate' => 0,
                'overall_average_duration' => 0,
                'overall_average_cost' => 0,
                'tasks_completed' => count($data['tasks'])
            ];

            $successRates = [];
            $durations = [];
            $costs = [];

            foreach ($data['tasks'] as $task => $analysis) {
                $successRates[] = $analysis['success_rate'];
                $durations[] = $analysis['average_duration'];
                $costs[] = $analysis['average_cost'];
            }

            if (!empty($successRates)) {
                $summary[$modelName]['overall_success_rate'] = round(array_sum($successRates) / count($successRates), 2);
            }
            if (!empty($durations)) {
                $summary[$modelName]['overall_average_duration'] = round(array_sum($durations) / count($durations), 2);
            }
            if (!empty($costs)) {
                $summary[$modelName]['overall_average_cost'] = round(array_sum($costs) / count($costs), 4);
            }
        }

        return $summary;
    }

    /**
     * Salva l'utilizzo nel database
     */
    private function saveUsage(string $requestId, $model, string $prompt, array $response, bool $success): void
    {
        if (!$this->config['monitoring']['save_to_database']) {
            return;
        }

        try {
            ModelUsage::create([
                'request_id' => $requestId,
                'model_name' => $model ? $model->getName() : 'unknown',
                'provider' => $model ? $model->getProvider() : 'unknown',
                'prompt' => $prompt,
                'response' => $response,
                'success' => $success,
                'duration' => $response['duration'] ?? 0,
                'cost' => $response['cost'] ?? 0,
                'tokens_used' => $response['tokens_used'] ?? 0
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save model usage', [
                'request_id' => $requestId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
