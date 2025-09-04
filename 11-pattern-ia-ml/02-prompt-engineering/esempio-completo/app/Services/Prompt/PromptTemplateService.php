<?php

namespace App\Services\Prompt;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\PromptTemplate;
use App\Models\PromptTest;

class PromptTemplateService
{
    private PromptVariableService $variableService;
    private PromptValidationService $validationService;
    private PromptOptimizationService $optimizationService;
    private array $config;

    public function __construct(
        PromptVariableService $variableService,
        PromptValidationService $validationService,
        PromptOptimizationService $optimizationService
    ) {
        $this->variableService = $variableService;
        $this->validationService = $validationService;
        $this->optimizationService = $optimizationService;
        $this->config = config('prompt', []);
    }

    /**
     * Genera contenuto usando un template
     */
    public function generate(string $templateName, array $variables = [], array $options = []): array
    {
        $startTime = microtime(true);
        $requestId = uniqid();

        Log::info('Prompt Template Generation Started', [
            'request_id' => $requestId,
            'template' => $templateName,
            'variables_count' => count($variables)
        ]);

        try {
            // Carica template
            $template = $this->loadTemplate($templateName);
            
            if (!$template) {
                throw new \Exception("Template '{$templateName}' non trovato");
            }

            // Valida variabili
            $this->variableService->validateVariables($templateName, $variables);

            // Genera prompt finale
            $finalPrompt = $this->buildPrompt($template, $variables);

            // Controlla cache
            $cacheKey = $this->generateCacheKey($templateName, $variables, $options);
            $cached = Cache::get($cacheKey);
            
            if ($cached && $this->config['cache_enabled']) {
                Log::info('Prompt Cache Hit', ['request_id' => $requestId]);
                return array_merge($cached, ['cached' => true]);
            }

            // Chiama AI provider
            $aiResponse = $this->callAIProvider($finalPrompt, $options);
            $duration = microtime(true) - $startTime;

            // Valida output
            $validationResult = $this->validationService->validateOutput(
                $aiResponse['text'],
                $templateName,
                $variables
            );

            // Calcola metriche
            $metrics = $this->calculateMetrics($aiResponse, $duration, $validationResult);

            $result = [
                'text' => $aiResponse['text'],
                'template' => $templateName,
                'variables' => $variables,
                'validation' => $validationResult,
                'metrics' => $metrics,
                'duration' => $duration,
                'cost' => $aiResponse['cost'] ?? 0,
                'tokens_used' => $aiResponse['tokens_used'] ?? 0,
                'cached' => false,
                'request_id' => $requestId
            ];

            // Cache risultato
            if ($this->config['cache_enabled']) {
                Cache::put($cacheKey, $result, $this->config['cache_ttl']);
            }

            // Salva nel database
            $this->saveGeneration($templateName, $variables, $result, $validationResult);

            Log::info('Prompt Template Generation Completed', [
                'request_id' => $requestId,
                'template' => $templateName,
                'duration' => $duration,
                'validation_passed' => $validationResult['passed']
            ]);

            return $result;

        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            
            Log::error('Prompt Template Generation Failed', [
                'request_id' => $requestId,
                'template' => $templateName,
                'error' => $e->getMessage(),
                'duration' => $duration
            ]);

            throw $e;
        }
    }

    /**
     * Testa un template con A/B testing
     */
    public function testTemplate(string $templateName, array $variables = [], array $options = []): array
    {
        $testId = uniqid();
        $iterations = $options['iterations'] ?? 5;
        $results = [];

        Log::info('Prompt Template Test Started', [
            'test_id' => $testId,
            'template' => $templateName,
            'iterations' => $iterations
        ]);

        for ($i = 0; $i < $iterations; $i++) {
            try {
                $result = $this->generate($templateName, $variables, $options);
                $results[] = $result;
            } catch (\Exception $e) {
                Log::warning('Prompt Test Iteration Failed', [
                    'test_id' => $testId,
                    'iteration' => $i,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Analizza risultati
        $analysis = $this->analyzeTestResults($results);

        // Salva test
        $this->saveTest($testId, $templateName, $variables, $results, $analysis);

        return [
            'test_id' => $testId,
            'template' => $templateName,
            'iterations' => $iterations,
            'results' => $results,
            'analysis' => $analysis
        ];
    }

    /**
     * Esegue A/B test tra due template
     */
    public function runABTest(array $testConfig): array
    {
        $testId = uniqid();
        $templateA = $testConfig['template_a'];
        $templateB = $testConfig['template_b'];
        $variables = $testConfig['variables'] ?? [];
        $iterations = $testConfig['iterations'] ?? 10;

        Log::info('Prompt A/B Test Started', [
            'test_id' => $testId,
            'template_a' => $templateA,
            'template_b' => $templateB,
            'iterations' => $iterations
        ]);

        $resultsA = [];
        $resultsB = [];

        // Testa template A
        for ($i = 0; $i < $iterations; $i++) {
            try {
                $result = $this->generate($templateA, $variables);
                $resultsA[] = $result;
            } catch (\Exception $e) {
                Log::warning('A/B Test Template A Failed', [
                    'test_id' => $testId,
                    'iteration' => $i,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Testa template B
        for ($i = 0; $i < $iterations; $i++) {
            try {
                $result = $this->generate($templateB, $variables);
                $resultsB[] = $result;
            } catch (\Exception $e) {
                Log::warning('A/B Test Template B Failed', [
                    'test_id' => $testId,
                    'iteration' => $i,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Analizza risultati
        $analysis = $this->compareTestResults($resultsA, $resultsB);

        return [
            'test_id' => $testId,
            'template_a' => $templateA,
            'template_b' => $templateB,
            'results_a' => $resultsA,
            'results_b' => $resultsB,
            'analysis' => $analysis
        ];
    }

    /**
     * Ottimizza un template basato su metriche
     */
    public function optimizeTemplate(string $templateName, array $optimizationOptions = []): array
    {
        Log::info('Prompt Template Optimization Started', [
            'template' => $templateName
        ]);

        // Carica template esistente
        $template = $this->loadTemplate($templateName);
        if (!$template) {
            throw new \Exception("Template '{$templateName}' non trovato");
        }

        // Ottimizza template
        $optimizedTemplate = $this->optimizationService->optimizeTemplate(
            $template,
            $optimizationOptions
        );

        // Testa template ottimizzato
        $testResults = $this->testTemplate($templateName, [], [
            'iterations' => 5,
            'template_override' => $optimizedTemplate
        ]);

        return [
            'original_template' => $template,
            'optimized_template' => $optimizedTemplate,
            'test_results' => $testResults,
            'improvement' => $this->calculateImprovement($template, $optimizedTemplate, $testResults)
        ];
    }

    /**
     * Carica template dal database o configurazione
     */
    private function loadTemplate(string $templateName): ?array
    {
        // Prima cerca nel database
        $dbTemplate = PromptTemplate::where('name', $templateName)->first();
        if ($dbTemplate) {
            return [
                'name' => $dbTemplate->name,
                'template' => $dbTemplate->template,
                'variables' => $dbTemplate->variables,
                'validation_rules' => $dbTemplate->validation_rules,
                'source' => 'database'
            ];
        }

        // Poi cerca nella configurazione
        $configTemplate = $this->config['templates'][$templateName] ?? null;
        if ($configTemplate) {
            $templateClass = new $configTemplate['class']();
            return [
                'name' => $templateName,
                'template' => $templateClass->getTemplate(),
                'variables' => $configTemplate['variables'],
                'validation_rules' => $configTemplate['validation_rules'],
                'source' => 'config'
            ];
        }

        return null;
    }

    /**
     * Costruisce il prompt finale sostituendo le variabili
     */
    private function buildPrompt(array $template, array $variables): string
    {
        $prompt = $template['template'];

        // Sostituisci variabili
        foreach ($variables as $key => $value) {
            $placeholder = "{{$key}}";
            $prompt = str_replace($placeholder, $value, $prompt);
        }

        // Valida che tutte le variabili siano state sostituite
        $missingVariables = $this->findMissingVariables($prompt);
        if (!empty($missingVariables)) {
            throw new \Exception("Variabili mancanti: " . implode(', ', $missingVariables));
        }

        return $prompt;
    }

    /**
     * Trova variabili non sostituite nel prompt
     */
    private function findMissingVariables(string $prompt): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $prompt, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Chiama il provider AI
     */
    private function callAIProvider(string $prompt, array $options): array
    {
        // Integrazione con AI Gateway Service
        $aiService = app(\App\Services\AI\AIGatewayService::class);
        
        return $aiService->generateText($prompt, $options);
    }

    /**
     * Genera chiave cache
     */
    private function generateCacheKey(string $templateName, array $variables, array $options): string
    {
        $keyData = [
            'template' => $templateName,
            'variables' => $variables,
            'options' => $options
        ];
        
        return 'prompt_template:' . md5(json_encode($keyData));
    }

    /**
     * Calcola metriche di performance
     */
    private function calculateMetrics(array $aiResponse, float $duration, array $validationResult): array
    {
        return [
            'quality_score' => $validationResult['quality_score'] ?? 0,
            'validation_passed' => $validationResult['passed'] ?? false,
            'response_time' => $duration,
            'cost_efficiency' => $aiResponse['cost'] / max($validationResult['quality_score'] ?? 1, 0.1),
            'tokens_per_second' => ($aiResponse['tokens_used'] ?? 0) / max($duration, 0.1)
        ];
    }

    /**
     * Analizza risultati di test
     */
    private function analyzeTestResults(array $results): array
    {
        if (empty($results)) {
            return ['error' => 'Nessun risultato da analizzare'];
        }

        $qualityScores = array_column($results, 'metrics.quality_score');
        $durations = array_column($results, 'duration');
        $costs = array_column($results, 'cost');
        $validationPassed = array_column($results, 'validation.passed');

        return [
            'total_tests' => count($results),
            'successful_tests' => count(array_filter($validationPassed)),
            'average_quality_score' => array_sum($qualityScores) / count($qualityScores),
            'min_quality_score' => min($qualityScores),
            'max_quality_score' => max($qualityScores),
            'average_duration' => array_sum($durations) / count($durations),
            'average_cost' => array_sum($costs) / count($costs),
            'success_rate' => count(array_filter($validationPassed)) / count($validationPassed) * 100
        ];
    }

    /**
     * Confronta risultati di A/B test
     */
    private function compareTestResults(array $resultsA, array $resultsB): array
    {
        $analysisA = $this->analyzeTestResults($resultsA);
        $analysisB = $this->analyzeTestResults($resultsB);

        return [
            'template_a' => $analysisA,
            'template_b' => $analysisB,
            'winner' => $analysisA['average_quality_score'] > $analysisB['average_quality_score'] ? 'A' : 'B',
            'improvement' => abs($analysisA['average_quality_score'] - $analysisB['average_quality_score']),
            'confidence' => $this->calculateConfidence($analysisA, $analysisB)
        ];
    }

    /**
     * Calcola livello di confidenza per A/B test
     */
    private function calculateConfidence(array $analysisA, array $analysisB): float
    {
        // Implementazione semplificata - in produzione useresti test statistici
        $diff = abs($analysisA['average_quality_score'] - $analysisB['average_quality_score']);
        $minSamples = min($analysisA['total_tests'], $analysisB['total_tests']);
        
        if ($minSamples < 10) {
            return 0.5; // Bassa confidenza con pochi campioni
        }
        
        return min(0.95, 0.5 + ($diff * 0.1) + ($minSamples * 0.01));
    }

    /**
     * Salva generazione nel database
     */
    private function saveGeneration(string $templateName, array $variables, array $result, array $validationResult): void
    {
        try {
            PromptTemplate::create([
                'name' => $templateName,
                'variables' => $variables,
                'result' => $result,
                'validation_result' => $validationResult,
                'success' => $validationResult['passed'] ?? false,
                'quality_score' => $validationResult['quality_score'] ?? 0,
                'cost' => $result['cost'] ?? 0,
                'duration' => $result['duration'] ?? 0
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save prompt generation', [
                'template' => $templateName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Salva test nel database
     */
    private function saveTest(string $testId, string $templateName, array $variables, array $results, array $analysis): void
    {
        try {
            PromptTest::create([
                'test_id' => $testId,
                'template_name' => $templateName,
                'variables' => $variables,
                'results' => $results,
                'analysis' => $analysis,
                'success_rate' => $analysis['success_rate'] ?? 0,
                'average_quality' => $analysis['average_quality_score'] ?? 0
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save prompt test', [
                'test_id' => $testId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Calcola miglioramento dopo ottimizzazione
     */
    private function calculateImprovement(array $originalTemplate, array $optimizedTemplate, array $testResults): array
    {
        // Implementazione semplificata
        return [
            'quality_improvement' => 0.1, // 10% miglioramento
            'cost_reduction' => 0.05, // 5% riduzione costi
            'speed_improvement' => 0.15 // 15% miglioramento velocità
        ];
    }
}
