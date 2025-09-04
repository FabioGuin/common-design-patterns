<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\AIModel;

class AIModelRegistry
{
    private array $models = [];
    private array $config;

    public function __construct()
    {
        $this->config = config('ai_models', []);
        $this->initializeModels();
    }

    /**
     * Inizializza tutti i modelli disponibili
     */
    private function initializeModels(): void
    {
        $models = $this->config['models'] ?? [];
        
        foreach ($models as $name => $config) {
            if (!$config['enabled']) {
                continue;
            }

            try {
                $model = $this->createModel($name, $config);
                $this->models[$name] = $model;
                
                Log::info('AI Model registered', [
                    'name' => $name,
                    'provider' => $config['provider']
                ]);
                
            } catch (\Exception $e) {
                Log::error('Failed to register AI model', [
                    'name' => $name,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Crea un'istanza del modello
     */
    private function createModel(string $name, array $config): AIModelInterface
    {
        $provider = $config['provider'];
        $providerClass = $this->getProviderClass($provider);
        
        return new $providerClass($name, $config);
    }

    /**
     * Ottiene la classe provider
     */
    private function getProviderClass(string $provider): string
    {
        $providers = [
            'openai' => \App\Services\AI\Providers\OpenAIProvider::class,
            'claude' => \App\Services\AI\Providers\ClaudeProvider::class,
            'gemini' => \App\Services\AI\Providers\GeminiProvider::class,
            'huggingface' => \App\Services\AI\Providers\HuggingFaceProvider::class,
        ];

        if (!isset($providers[$provider])) {
            throw new \Exception("Provider '{$provider}' non supportato");
        }

        return $providers[$provider];
    }

    /**
     * Ottiene un modello per nome
     */
    public function getModel(string $name): ?AIModelInterface
    {
        return $this->models[$name] ?? null;
    }

    /**
     * Ottiene tutti i modelli disponibili
     */
    public function getAllModels(): array
    {
        return $this->models;
    }

    /**
     * Ottiene modelli per provider
     */
    public function getModelsByProvider(string $provider): array
    {
        return array_filter($this->models, function($model) use ($provider) {
            return $model->getProvider() === $provider;
        });
    }

    /**
     * Ottiene modelli per capacità
     */
    public function getModelsByCapability(string $capability): array
    {
        return array_filter($this->models, function($model) use ($capability) {
            return in_array($capability, $model->getCapabilities());
        });
    }

    /**
     * Ottiene modelli disponibili (non in errore)
     */
    public function getAvailableModels(): array
    {
        return array_filter($this->models, function($model) {
            return $model->isAvailable();
        });
    }

    /**
     * Ottiene modelli per task specifico
     */
    public function getModelsForTask(string $task): array
    {
        $taskMapping = $this->config['task_mapping'][$task] ?? [];
        $preferredModels = $taskMapping['preferred_models'] ?? [];
        $fallbackModels = $taskMapping['fallback_models'] ?? [];
        
        $allModels = array_merge($preferredModels, $fallbackModels);
        $availableModels = [];
        
        foreach ($allModels as $modelName) {
            $model = $this->getModel($modelName);
            if ($model && $model->isAvailable()) {
                $availableModels[] = $model;
            }
        }
        
        return $availableModels;
    }

    /**
     * Ottiene modelli ordinati per priorità
     */
    public function getModelsByPriority(): array
    {
        $models = $this->models;
        
        usort($models, function($a, $b) {
            return $a->getPriority() - $b->getPriority();
        });
        
        return $models;
    }

    /**
     * Ottiene modelli ordinati per costo
     */
    public function getModelsByCost(): array
    {
        $models = $this->models;
        
        usort($models, function($a, $b) {
            return $a->getCostPerToken() <=> $b->getCostPerToken();
        });
        
        return $models;
    }

    /**
     * Ottiene modelli ordinati per performance
     */
    public function getModelsByPerformance(): array
    {
        $models = $this->models;
        
        usort($models, function($a, $b) {
            return $b->getAverageResponseTime() <=> $a->getAverageResponseTime();
        });
        
        return $models;
    }

    /**
     * Verifica se un modello esiste
     */
    public function hasModel(string $name): bool
    {
        return isset($this->models[$name]);
    }

    /**
     * Ottiene statistiche dei modelli
     */
    public function getModelStats(): array
    {
        $stats = [
            'total_models' => count($this->models),
            'available_models' => count($this->getAvailableModels()),
            'providers' => [],
            'capabilities' => [],
            'models_by_priority' => []
        ];

        // Statistiche per provider
        foreach ($this->models as $model) {
            $provider = $model->getProvider();
            if (!isset($stats['providers'][$provider])) {
                $stats['providers'][$provider] = 0;
            }
            $stats['providers'][$provider]++;
        }

        // Statistiche per capacità
        foreach ($this->models as $model) {
            foreach ($model->getCapabilities() as $capability) {
                if (!isset($stats['capabilities'][$capability])) {
                    $stats['capabilities'][$capability] = 0;
                }
                $stats['capabilities'][$capability]++;
            }
        }

        // Modelli per priorità
        $modelsByPriority = $this->getModelsByPriority();
        foreach ($modelsByPriority as $model) {
            $priority = $model->getPriority();
            if (!isset($stats['models_by_priority'][$priority])) {
                $stats['models_by_priority'][$priority] = [];
            }
            $stats['models_by_priority'][$priority][] = $model->getName();
        }

        return $stats;
    }

    /**
     * Aggiorna lo stato di un modello
     */
    public function updateModelStatus(string $name, bool $available): void
    {
        if (isset($this->models[$name])) {
            $this->models[$name]->setAvailable($available);
            
            Log::info('Model status updated', [
                'name' => $name,
                'available' => $available
            ]);
        }
    }

    /**
     * Aggiorna le performance di un modello
     */
    public function updateModelPerformance(string $name, float $responseTime, bool $success): void
    {
        if (isset($this->models[$name])) {
            $this->models[$name]->updatePerformance($responseTime, $success);
        }
    }

    /**
     * Ottiene i modelli consigliati per un task
     */
    public function getRecommendedModels(string $task, array $constraints = []): array
    {
        $models = $this->getModelsForTask($task);
        
        // Applica vincoli
        if (isset($constraints['max_cost'])) {
            $models = array_filter($models, function($model) use ($constraints) {
                return $model->getCostPerToken() <= $constraints['max_cost'];
            });
        }
        
        if (isset($constraints['max_duration'])) {
            $models = array_filter($models, function($model) use ($constraints) {
                return $model->getAverageResponseTime() <= $constraints['max_duration'];
            });
        }
        
        if (isset($constraints['required_capabilities'])) {
            $models = array_filter($models, function($model) use ($constraints) {
                $modelCapabilities = $model->getCapabilities();
                return !array_diff($constraints['required_capabilities'], $modelCapabilities);
            });
        }
        
        // Ordina per priorità
        usort($models, function($a, $b) {
            return $a->getPriority() - $b->getPriority();
        });
        
        return $models;
    }

    /**
     * Ottiene il modello migliore per un task
     */
    public function getBestModelForTask(string $task, array $constraints = []): ?AIModelInterface
    {
        $models = $this->getRecommendedModels($task, $constraints);
        
        return !empty($models) ? $models[0] : null;
    }

    /**
     * Ottiene informazioni dettagliate su un modello
     */
    public function getModelInfo(string $name): ?array
    {
        $model = $this->getModel($name);
        
        if (!$model) {
            return null;
        }
        
        return [
            'name' => $model->getName(),
            'provider' => $model->getProvider(),
            'description' => $model->getDescription(),
            'capabilities' => $model->getCapabilities(),
            'cost_per_token' => $model->getCostPerToken(),
            'max_tokens' => $model->getMaxTokens(),
            'context_window' => $model->getContextWindow(),
            'priority' => $model->getPriority(),
            'available' => $model->isAvailable(),
            'average_response_time' => $model->getAverageResponseTime(),
            'success_rate' => $model->getSuccessRate(),
            'tags' => $model->getTags()
        ];
    }

    /**
     * Ottiene tutti i modelli con informazioni dettagliate
     */
    public function getAllModelsInfo(): array
    {
        $modelsInfo = [];
        
        foreach ($this->models as $name => $model) {
            $modelsInfo[$name] = $this->getModelInfo($name);
        }
        
        return $modelsInfo;
    }

    /**
     * Ricarica i modelli dalla configurazione
     */
    public function reload(): void
    {
        $this->models = [];
        $this->initializeModels();
        
        Log::info('AI Model Registry reloaded');
    }

    /**
     * Ottiene i modelli per un range di costo
     */
    public function getModelsByCostRange(float $minCost, float $maxCost): array
    {
        return array_filter($this->models, function($model) use ($minCost, $maxCost) {
            $cost = $model->getCostPerToken();
            return $cost >= $minCost && $cost <= $maxCost;
        });
    }

    /**
     * Ottiene i modelli per un range di performance
     */
    public function getModelsByPerformanceRange(float $minResponseTime, float $maxResponseTime): array
    {
        return array_filter($this->models, function($model) use ($minResponseTime, $maxResponseTime) {
            $responseTime = $model->getAverageResponseTime();
            return $responseTime >= $minResponseTime && $responseTime <= $maxResponseTime;
        });
    }
}
