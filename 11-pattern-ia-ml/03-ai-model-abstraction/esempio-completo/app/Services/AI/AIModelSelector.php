<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AIModelSelector
{
    private AIModelRegistry $registry;
    private array $config;

    public function __construct(AIModelRegistry $registry)
    {
        $this->registry = $registry;
        $this->config = config('ai_models', []);
    }

    /**
     * Seleziona il modello migliore per un task
     */
    public function selectBestModel(string $task, array $options = []): ?AIModelInterface
    {
        $strategy = $options['strategy'] ?? $this->config['default_strategy'];
        $constraints = $this->extractConstraints($options);
        
        Log::info('AI Model Selection Started', [
            'task' => $task,
            'strategy' => $strategy,
            'constraints' => $constraints
        ]);

        try {
            // Ottieni modelli candidati
            $candidates = $this->getCandidateModels($task, $constraints);
            
            if (empty($candidates)) {
                Log::warning('No candidate models found', [
                    'task' => $task,
                    'constraints' => $constraints
                ]);
                return null;
            }

            // Applica la strategia di selezione
            $selectedModel = $this->applySelectionStrategy($candidates, $strategy, $constraints);
            
            Log::info('AI Model Selected', [
                'task' => $task,
                'strategy' => $strategy,
                'selected_model' => $selectedModel ? $selectedModel->getName() : 'none',
                'candidates_count' => count($candidates)
            ]);

            return $selectedModel;

        } catch (\Exception $e) {
            Log::error('AI Model Selection Failed', [
                'task' => $task,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Seleziona modelli per un task specifico
     */
    public function selectModelsForTask(string $task, int $count = 3, array $options = []): array
    {
        $strategy = $options['strategy'] ?? $this->config['default_strategy'];
        $constraints = $this->extractConstraints($options);
        
        $candidates = $this->getCandidateModels($task, $constraints);
        
        if (empty($candidates)) {
            return [];
        }

        // Ordina i candidati secondo la strategia
        $sortedCandidates = $this->sortCandidatesByStrategy($candidates, $strategy, $constraints);
        
        // Restituisci i primi N modelli
        return array_slice($sortedCandidates, 0, $count);
    }

    /**
     * Seleziona il modello più economico
     */
    public function selectCheapestModel(string $task, array $constraints = []): ?AIModelInterface
    {
        $candidates = $this->getCandidateModels($task, $constraints);
        
        if (empty($candidates)) {
            return null;
        }

        // Ordina per costo crescente
        usort($candidates, function($a, $b) {
            return $a->getCostPerToken() <=> $b->getCostPerToken();
        });

        return $candidates[0];
    }

    /**
     * Seleziona il modello più veloce
     */
    public function selectFastestModel(string $task, array $constraints = []): ?AIModelInterface
    {
        $candidates = $this->getCandidateModels($task, $constraints);
        
        if (empty($candidates)) {
            return null;
        }

        // Ordina per tempo di risposta crescente
        usort($candidates, function($a, $b) {
            return $a->getAverageResponseTime() <=> $b->getAverageResponseTime();
        });

        return $candidates[0];
    }

    /**
     * Seleziona il modello più affidabile
     */
    public function selectMostReliableModel(string $task, array $constraints = []): ?AIModelInterface
    {
        $candidates = $this->getCandidateModels($task, $constraints);
        
        if (empty($candidates)) {
            return null;
        }

        // Ordina per success rate decrescente
        usort($candidates, function($a, $b) {
            return $b->getSuccessRate() <=> $a->getSuccessRate();
        });

        return $candidates[0];
    }

    /**
     * Seleziona modelli per capacità specifiche
     */
    public function selectModelsByCapabilities(array $capabilities, array $constraints = []): array
    {
        $models = $this->registry->getAllModels();
        $candidates = [];

        foreach ($models as $model) {
            if (!$model->isAvailable()) {
                continue;
            }

            // Verifica se il modello ha tutte le capacità richieste
            $modelCapabilities = $model->getCapabilities();
            if (array_diff($capabilities, $modelCapabilities)) {
                continue;
            }

            // Applica vincoli
            if (!$this->meetsConstraints($model, $constraints)) {
                continue;
            }

            $candidates[] = $model;
        }

        return $candidates;
    }

    /**
     * Ottiene i modelli candidati per un task
     */
    private function getCandidateModels(string $task, array $constraints = []): array
    {
        // Ottieni modelli per task
        $taskModels = $this->registry->getModelsForTask($task);
        
        // Filtra per vincoli
        $candidates = [];
        foreach ($taskModels as $model) {
            if ($this->meetsConstraints($model, $constraints)) {
                $candidates[] = $model;
            }
        }

        // Se non ci sono candidati per il task, prova con modelli generali
        if (empty($candidates)) {
            $generalModels = $this->registry->getModelsByCapability('text_generation');
            foreach ($generalModels as $model) {
                if ($this->meetsConstraints($model, $constraints)) {
                    $candidates[] = $model;
                }
            }
        }

        return $candidates;
    }

    /**
     * Applica la strategia di selezione
     */
    private function applySelectionStrategy(array $candidates, string $strategy, array $constraints): ?AIModelInterface
    {
        if (empty($candidates)) {
            return null;
        }

        $strategyConfig = $this->config['strategies'][$strategy] ?? $this->config['strategies']['balanced'];
        
        // Calcola score per ogni candidato
        $scoredCandidates = [];
        foreach ($candidates as $model) {
            $score = $this->calculateModelScore($model, $strategyConfig, $constraints);
            $scoredCandidates[] = [
                'model' => $model,
                'score' => $score
            ];
        }

        // Ordina per score decrescente
        usort($scoredCandidates, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $scoredCandidates[0]['model'];
    }

    /**
     * Calcola lo score di un modello
     */
    private function calculateModelScore(AIModelInterface $model, array $strategyConfig, array $constraints): float
    {
        $costWeight = $strategyConfig['cost_weight'] ?? 0.3;
        $performanceWeight = $strategyConfig['performance_weight'] ?? 0.4;
        $availabilityWeight = $strategyConfig['availability_weight'] ?? 0.3;

        // Normalizza i valori
        $costScore = $this->normalizeCostScore($model->getCostPerToken());
        $performanceScore = $this->normalizePerformanceScore($model->getAverageResponseTime());
        $availabilityScore = $model->getSuccessRate() / 100;

        // Calcola score ponderato
        $score = ($costScore * $costWeight) + 
                 ($performanceScore * $performanceWeight) + 
                 ($availabilityScore * $availabilityWeight);

        // Applica bonus per priorità
        $priorityBonus = (6 - $model->getPriority()) * 0.1;
        $score += $priorityBonus;

        return $score;
    }

    /**
     * Normalizza il score del costo (più basso = migliore)
     */
    private function normalizeCostScore(float $cost): float
    {
        // Trova il range di costi tra tutti i modelli
        $allModels = $this->registry->getAllModels();
        $costs = array_map(fn($m) => $m->getCostPerToken(), $allModels);
        
        if (empty($costs)) {
            return 0.5;
        }

        $minCost = min($costs);
        $maxCost = max($costs);
        
        if ($maxCost == $minCost) {
            return 0.5;
        }

        // Normalizza (costo più basso = score più alto)
        return 1 - (($cost - $minCost) / ($maxCost - $minCost));
    }

    /**
     * Normalizza il score delle performance (più veloce = migliore)
     */
    private function normalizePerformanceScore(float $responseTime): float
    {
        // Trova il range di tempi di risposta
        $allModels = $this->registry->getAllModels();
        $responseTimes = array_map(fn($m) => $m->getAverageResponseTime(), $allModels);
        
        if (empty($responseTimes)) {
            return 0.5;
        }

        $minTime = min($responseTimes);
        $maxTime = max($responseTimes);
        
        if ($maxTime == $minTime) {
            return 0.5;
        }

        // Normalizza (tempo più basso = score più alto)
        return 1 - (($responseTime - $minTime) / ($maxTime - $minTime));
    }

    /**
     * Verifica se un modello soddisfa i vincoli
     */
    private function meetsConstraints(AIModelInterface $model, array $constraints): bool
    {
        // Vincolo di costo massimo
        if (isset($constraints['max_cost']) && $model->getCostPerToken() > $constraints['max_cost']) {
            return false;
        }

        // Vincolo di durata massima
        if (isset($constraints['max_duration']) && $model->getAverageResponseTime() > $constraints['max_duration']) {
            return false;
        }

        // Vincolo di capacità richieste
        if (isset($constraints['required_capabilities'])) {
            $modelCapabilities = $model->getCapabilities();
            if (array_diff($constraints['required_capabilities'], $modelCapabilities)) {
                return false;
            }
        }

        // Vincolo di provider specifico
        if (isset($constraints['provider']) && $model->getProvider() !== $constraints['provider']) {
            return false;
        }

        // Vincolo di priorità massima
        if (isset($constraints['max_priority']) && $model->getPriority() > $constraints['max_priority']) {
            return false;
        }

        return true;
    }

    /**
     * Estrae i vincoli dalle opzioni
     */
    private function extractConstraints(array $options): array
    {
        $constraints = [];

        if (isset($options['max_cost'])) {
            $constraints['max_cost'] = $options['max_cost'];
        }

        if (isset($options['max_duration'])) {
            $constraints['max_duration'] = $options['max_duration'];
        }

        if (isset($options['required_capabilities'])) {
            $constraints['required_capabilities'] = $options['required_capabilities'];
        }

        if (isset($options['provider'])) {
            $constraints['provider'] = $options['provider'];
        }

        if (isset($options['max_priority'])) {
            $constraints['max_priority'] = $options['max_priority'];
        }

        return $constraints;
    }

    /**
     * Ordina i candidati secondo la strategia
     */
    private function sortCandidatesByStrategy(array $candidates, string $strategy, array $constraints): array
    {
        $strategyConfig = $this->config['strategies'][$strategy] ?? $this->config['strategies']['balanced'];
        
        usort($candidates, function($a, $b) use ($strategyConfig, $constraints) {
            $scoreA = $this->calculateModelScore($a, $strategyConfig, $constraints);
            $scoreB = $this->calculateModelScore($b, $strategyConfig, $constraints);
            
            return $scoreB <=> $scoreA;
        });

        return $candidates;
    }

    /**
     * Ottiene statistiche di selezione
     */
    public function getSelectionStats(): array
    {
        $cacheKey = 'ai_model_selection_stats';
        
        return Cache::remember($cacheKey, 300, function() {
            $stats = [
                'total_selections' => 0,
                'selections_by_strategy' => [],
                'selections_by_task' => [],
                'most_selected_models' => [],
                'average_selection_time' => 0
            ];

            // Queste statistiche potrebbero essere salvate nel database
            // per ora restituiamo dati di esempio
            return $stats;
        });
    }

    /**
     * Aggiorna le statistiche di selezione
     */
    public function updateSelectionStats(string $strategy, string $task, string $modelName, float $selectionTime): void
    {
        // Implementa l'aggiornamento delle statistiche
        // Potrebbe essere salvato nel database o cache
    }
}
