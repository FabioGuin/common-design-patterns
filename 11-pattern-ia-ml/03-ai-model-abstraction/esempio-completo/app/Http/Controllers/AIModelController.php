<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AI\AIModelService;
use App\Services\AI\AIModelRegistry;
use App\Services\AI\AIModelSelector;
use App\Services\AI\ModelPerformanceTracker;
use App\Models\AIModel;
use App\Models\ModelUsage;
use App\Models\ModelPerformance;

class AIModelController extends Controller
{
    private AIModelService $modelService;
    private AIModelRegistry $registry;
    private AIModelSelector $selector;
    private ModelPerformanceTracker $performanceTracker;

    public function __construct(
        AIModelService $modelService,
        AIModelRegistry $registry,
        AIModelSelector $selector,
        ModelPerformanceTracker $performanceTracker
    ) {
        $this->modelService = $modelService;
        $this->registry = $registry;
        $this->selector = $selector;
        $this->performanceTracker = $performanceTracker;
    }

    /**
     * Dashboard principale
     */
    public function dashboard()
    {
        $models = $this->registry->getAllModelsInfo();
        $stats = $this->performanceTracker->getAggregateStats();
        $recentUsage = ModelUsage::latest()->limit(10)->get();
        $topModels = $this->performanceTracker->getMostUsedModels(5);

        return view('ai-models.dashboard', compact('models', 'stats', 'recentUsage', 'topModels'));
    }

    /**
     * Confronto modelli
     */
    public function modelComparison()
    {
        $models = $this->registry->getAllModelsInfo();
        $providers = array_unique(array_column($models, 'provider'));
        $capabilities = array_unique(array_merge(...array_column($models, 'capabilities')));

        return view('ai-models.model-comparison', compact('models', 'providers', 'capabilities'));
    }

    /**
     * Performance e analytics
     */
    public function performance()
    {
        $stats = $this->performanceTracker->getAggregateStats();
        $dailyStats = $this->performanceTracker->getPerformanceByPeriod(7);
        $topModels = $this->performanceTracker->getMostUsedModels(10);
        $bestPerforming = $this->performanceTracker->getBestPerformingModels(10);
        $realTimeStats = $this->performanceTracker->getRealTimeMetrics();

        return view('ai-models.performance', compact('stats', 'dailyStats', 'topModels', 'bestPerforming', 'realTimeStats'));
    }

    /**
     * Genera testo usando il modello migliore
     */
    public function generateText(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:10000',
            'task' => 'string|in:text_generation,translation,analysis,summarization',
            'strategy' => 'string|in:balanced,cost_optimized,performance_optimized,reliability_optimized',
            'max_cost' => 'numeric|min:0',
            'max_duration' => 'numeric|min:0',
            'required_capabilities' => 'array',
            'provider' => 'string'
        ]);

        try {
            $options = $request->only([
                'task', 'strategy', 'max_cost', 'max_duration', 
                'required_capabilities', 'provider', 'temperature', 
                'max_tokens', 'top_p'
            ]);

            $result = $this->modelService->generateText($request->prompt, $options);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera immagine
     */
    public function generateImage(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'size' => 'string|in:256x256,512x512,1024x1024',
            'quality' => 'string|in:standard,hd',
            'style' => 'string|in:vivid,natural'
        ]);

        try {
            $options = $request->only(['size', 'quality', 'style', 'n']);

            $result = $this->modelService->generateImage($request->prompt, $options);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traduce testo
     */
    public function translate(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:5000',
            'target_language' => 'required|string|max:10',
            'optimize_for' => 'string|in:cost,performance,quality'
        ]);

        try {
            $options = $request->only(['optimize_for']);

            $result = $this->modelService->translate($request->text, $request->target_language, $options);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Analizza contenuto
     */
    public function analyzeContent(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:10000',
            'analysis_type' => 'required|string|max:100',
            'strategy' => 'string|in:balanced,cost_optimized,performance_optimized'
        ]);

        try {
            $options = $request->only(['strategy', 'max_cost', 'max_duration']);

            $result = $this->modelService->analyzeContent($request->content, $request->analysis_type, $options);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista modelli disponibili
     */
    public function getModels(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['provider', 'capability', 'enabled', 'priority']);
            $models = $this->registry->getAllModelsInfo();

            // Applica filtri
            if (isset($filters['provider'])) {
                $models = array_filter($models, fn($m) => $m['provider'] === $filters['provider']);
            }

            if (isset($filters['capability'])) {
                $models = array_filter($models, fn($m) => in_array($filters['capability'], $m['capabilities']));
            }

            if (isset($filters['enabled'])) {
                $models = array_filter($models, fn($m) => $m['available'] === (bool)$filters['enabled']);
            }

            if (isset($filters['priority'])) {
                $models = array_filter($models, fn($m) => $m['priority'] <= $filters['priority']);
            }

            return response()->json([
                'success' => true,
                'data' => array_values($models)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confronta modelli
     */
    public function compareModels(Request $request): JsonResponse
    {
        $request->validate([
            'models' => 'required|array|min:2|max:5',
            'test_prompt' => 'required|string|max:1000',
            'iterations' => 'integer|min:1|max:10',
            'task' => 'string|in:text_generation,translation,analysis,summarization'
        ]);

        try {
            $options = $request->only(['test_prompt', 'iterations', 'task']);

            $result = $this->modelService->compareModels($request->models, $options);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Testa un modello specifico
     */
    public function testModel(Request $request): JsonResponse
    {
        $request->validate([
            'model_name' => 'required|string',
            'prompt' => 'string|max:1000',
            'iterations' => 'integer|min:1|max:20',
            'options' => 'array'
        ]);

        try {
            $options = $request->only(['prompt', 'iterations', 'options']);
            $options['prompt'] = $options['prompt'] ?? 'Test prompt per verificare il funzionamento del modello';
            $options['iterations'] = $options['iterations'] ?? 5;

            $result = $this->modelService->testModel($request->model_name, $options);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Esegue benchmark
     */
    public function runBenchmark(Request $request): JsonResponse
    {
        $request->validate([
            'models' => 'required|array|min:1',
            'tasks' => 'required|array|min:1',
            'iterations' => 'integer|min:1|max:10'
        ]);

        try {
            $config = $request->only(['models', 'tasks', 'iterations']);
            $config['iterations'] = $config['iterations'] ?? 5;

            $result = $this->modelService->runBenchmark($config);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene statistiche di performance
     */
    public function getPerformanceStats(Request $request): JsonResponse
    {
        try {
            $modelName = $request->get('model_name');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $stats = $this->modelService->getPerformanceStats($modelName, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene statistiche aggregate
     */
    public function getAggregateStats(): JsonResponse
    {
        try {
            $stats = $this->performanceTracker->getAggregateStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene statistiche per periodo
     */
    public function getPeriodStats(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:1|max:365',
            'model_name' => 'string'
        ]);

        try {
            $days = $request->get('days', 7);
            $modelName = $request->get('model_name');

            $stats = $this->performanceTracker->getPerformanceByPeriod($days);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene modelli più utilizzati
     */
    public function getMostUsedModels(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $models = $this->performanceTracker->getMostUsedModels($limit);

            return response()->json([
                'success' => true,
                'data' => $models
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene modelli con migliori performance
     */
    public function getBestPerformingModels(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $models = $this->performanceTracker->getBestPerformingModels($limit);

            return response()->json([
                'success' => true,
                'data' => $models
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene statistiche in tempo reale
     */
    public function getRealTimeStats(): JsonResponse
    {
        try {
            $stats = $this->performanceTracker->getRealTimeMetrics();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Seleziona il modello migliore per un task
     */
    public function selectBestModel(Request $request): JsonResponse
    {
        $request->validate([
            'task' => 'required|string',
            'strategy' => 'string|in:balanced,cost_optimized,performance_optimized,reliability_optimized',
            'constraints' => 'array'
        ]);

        try {
            $options = $request->only(['strategy', 'constraints']);
            $model = $this->selector->selectBestModel($request->task, $options);

            if (!$model) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nessun modello disponibile per il task richiesto'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'model' => $model->getName(),
                    'provider' => $model->getProvider(),
                    'description' => $model->getDescription(),
                    'capabilities' => $model->getCapabilities(),
                    'cost_per_token' => $model->getCostPerToken(),
                    'max_tokens' => $model->getMaxTokens(),
                    'priority' => $model->getPriority()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene informazioni dettagliate su un modello
     */
    public function getModelInfo(Request $request): JsonResponse
    {
        $request->validate([
            'model_name' => 'required|string'
        ]);

        try {
            $modelInfo = $this->registry->getModelInfo($request->model_name);

            if (!$modelInfo) {
                return response()->json([
                    'success' => false,
                    'error' => 'Modello non trovato'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $modelInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ottiene le statistiche del registry
     */
    public function getRegistryStats(): JsonResponse
    {
        try {
            $stats = $this->registry->getModelStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pulisce i dati vecchi
     */
    public function cleanupOldData(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:1|max:365'
        ]);

        try {
            $days = $request->get('days', 90);
            
            $usageDeleted = ModelUsage::cleanupOldData($days);
            $performanceDeleted = ModelPerformance::cleanupOldData($days);

            return response()->json([
                'success' => true,
                'data' => [
                    'usage_records_deleted' => $usageDeleted,
                    'performance_records_deleted' => $performanceDeleted,
                    'days_retained' => $days
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
