<?php

namespace App\Http\Controllers;

use App\Services\Prompt\PromptTemplateService;
use App\Services\Prompt\PromptValidationService;
use App\Services\Prompt\PromptOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PromptController extends Controller
{
    private PromptTemplateService $templateService;
    private PromptValidationService $validationService;
    private PromptOptimizationService $optimizationService;

    public function __construct(
        PromptTemplateService $templateService,
        PromptValidationService $validationService,
        PromptOptimizationService $optimizationService
    ) {
        $this->templateService = $templateService;
        $this->validationService = $validationService;
        $this->optimizationService = $optimizationService;
    }

    /**
     * Dashboard principale
     */
    public function dashboard(): View
    {
        $templates = $this->getAvailableTemplates();
        $recentTests = $this->getRecentTests();
        $analytics = $this->getAnalytics();

        return view('prompt.dashboard', compact(
            'templates',
            'recentTests',
            'analytics'
        ));
    }

    /**
     * Genera contenuto usando un template
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'template' => 'required|string',
            'variables' => 'required|array',
            'options' => 'sometimes|array'
        ]);

        try {
            $result = $this->templateService->generate(
                $request->input('template'),
                $request->input('variables'),
                $request->input('options', [])
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Testa un template
     */
    public function testTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'template' => 'required|string',
            'variables' => 'required|array',
            'iterations' => 'sometimes|integer|min:1|max:20',
            'options' => 'sometimes|array'
        ]);

        try {
            $result = $this->templateService->testTemplate(
                $request->input('template'),
                $request->input('variables'),
                array_merge($request->input('options', []), [
                    'iterations' => $request->input('iterations', 5)
                ])
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Esegue A/B test tra due template
     */
    public function runABTest(Request $request): JsonResponse
    {
        $request->validate([
            'template_a' => 'required|string',
            'template_b' => 'required|string',
            'variables' => 'required|array',
            'iterations' => 'sometimes|integer|min:5|max:50',
            'options' => 'sometimes|array'
        ]);

        try {
            $result = $this->templateService->runABTest([
                'template_a' => $request->input('template_a'),
                'template_b' => $request->input('template_b'),
                'variables' => $request->input('variables'),
                'iterations' => $request->input('iterations', 10),
                'options' => $request->input('options', [])
            ]);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Ottimizza un template
     */
    public function optimizeTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'template' => 'required|string',
            'optimization_options' => 'sometimes|array'
        ]);

        try {
            $result = $this->templateService->optimizeTemplate(
                $request->input('template'),
                $request->input('optimization_options', [])
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Lista template disponibili
     */
    public function getTemplates(): JsonResponse
    {
        $templates = $this->getAvailableTemplates();

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }

    /**
     * Ottiene dettagli di un template specifico
     */
    public function getTemplateDetails(string $templateName): JsonResponse
    {
        try {
            $template = $this->loadTemplate($templateName);
            
            if (!$template) {
                return response()->json([
                    'success' => false,
                    'error' => "Template '{$templateName}' non trovato"
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $template
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Valida un output
     */
    public function validateOutput(Request $request): JsonResponse
    {
        $request->validate([
            'output' => 'required|string',
            'template' => 'required|string',
            'variables' => 'sometimes|array'
        ]);

        try {
            $result = $this->validationService->validateOutput(
                $request->input('output'),
                $request->input('template'),
                $request->input('variables', [])
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Ottiene analytics e metriche
     */
    public function getAnalytics(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $template = $request->input('template');

        try {
            $analytics = $this->getAnalyticsData($startDate, $endDate, $template);

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Ottiene cronologia dei test
     */
    public function getTestHistory(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $template = $request->input('template');

        try {
            $tests = $this->getTestHistoryData($perPage, $template);

            return response()->json([
                'success' => true,
                'data' => $tests
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Editor per template
     */
    public function templateEditor(string $templateName = null): View
    {
        $template = null;
        if ($templateName) {
            $template = $this->loadTemplate($templateName);
        }

        $availableTemplates = $this->getAvailableTemplates();

        return view('prompt.template-editor', compact(
            'template',
            'availableTemplates'
        ));
    }

    /**
     * Salva un template personalizzato
     */
    public function saveTemplate(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'template' => 'required|string',
            'variables' => 'required|array',
            'validation_rules' => 'sometimes|array',
            'description' => 'sometimes|string|max:500'
        ]);

        try {
            // Salva nel database
            $template = \App\Models\PromptTemplate::updateOrCreate(
                ['name' => $request->input('name')],
                [
                    'template' => $request->input('template'),
                    'variables' => $request->input('variables'),
                    'validation_rules' => $request->input('validation_rules', []),
                    'description' => $request->input('description', ''),
                    'is_custom' => true
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $template
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Elimina un template personalizzato
     */
    public function deleteTemplate(string $templateName): JsonResponse
    {
        try {
            $template = \App\Models\PromptTemplate::where('name', $templateName)
                ->where('is_custom', true)
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'error' => "Template '{$templateName}' non trovato o non eliminabile"
                ], 404);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => "Template '{$templateName}' eliminato con successo"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Ottiene template disponibili
     */
    private function getAvailableTemplates(): array
    {
        $templates = [];
        $configTemplates = config('prompt.templates', []);

        foreach ($configTemplates as $name => $config) {
            $templates[] = [
                'name' => $name,
                'display_name' => $config['name'] ?? $name,
                'description' => $config['description'] ?? '',
                'variables' => $config['variables'] ?? [],
                'cost_estimate' => $config['cost_estimate'] ?? 0,
                'expected_duration' => $config['expected_duration'] ?? 0,
                'source' => 'config'
            ];
        }

        // Aggiungi template personalizzati dal database
        $customTemplates = \App\Models\PromptTemplate::where('is_custom', true)->get();
        foreach ($customTemplates as $template) {
            $templates[] = [
                'name' => $template->name,
                'display_name' => $template->name,
                'description' => $template->description,
                'variables' => $template->variables,
                'cost_estimate' => 0,
                'expected_duration' => 0,
                'source' => 'database'
            ];
        }

        return $templates;
    }

    /**
     * Carica un template specifico
     */
    private function loadTemplate(string $templateName): ?array
    {
        // Prima cerca nel database
        $dbTemplate = \App\Models\PromptTemplate::where('name', $templateName)->first();
        if ($dbTemplate) {
            return [
                'name' => $dbTemplate->name,
                'template' => $dbTemplate->template,
                'variables' => $dbTemplate->variables,
                'validation_rules' => $dbTemplate->validation_rules,
                'description' => $dbTemplate->description,
                'source' => 'database'
            ];
        }

        // Poi cerca nella configurazione
        $configTemplate = config("prompt.templates.{$templateName}");
        if ($configTemplate) {
            $templateClass = new $configTemplate['class']();
            return [
                'name' => $templateName,
                'template' => $templateClass->getTemplate(),
                'variables' => $configTemplate['variables'],
                'validation_rules' => $configTemplate['validation_rules'],
                'description' => $configTemplate['description'] ?? '',
                'source' => 'config'
            ];
        }

        return null;
    }

    /**
     * Ottiene test recenti
     */
    private function getRecentTests(): array
    {
        return \App\Models\PromptTest::orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($test) {
                return [
                    'test_id' => $test->test_id,
                    'template_name' => $test->template_name,
                    'success_rate' => $test->success_rate,
                    'average_quality' => $test->average_quality,
                    'created_at' => $test->created_at->toISOString()
                ];
            })
            ->toArray();
    }

    /**
     * Ottiene dati analytics
     */
    private function getAnalyticsData(?string $startDate = null, ?string $endDate = null, ?string $template = null): array
    {
        $query = \App\Models\PromptTemplate::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($template) {
            $query->where('name', $template);
        }

        $totalGenerations = $query->count();
        $successfulGenerations = $query->where('success', true)->count();
        $averageQuality = $query->avg('quality_score') ?? 0;
        $totalCost = $query->sum('cost') ?? 0;

        return [
            'total_generations' => $totalGenerations,
            'successful_generations' => $successfulGenerations,
            'success_rate' => $totalGenerations > 0 ? ($successfulGenerations / $totalGenerations) * 100 : 0,
            'average_quality' => round($averageQuality, 2),
            'total_cost' => round($totalCost, 4),
            'average_cost' => $totalGenerations > 0 ? round($totalCost / $totalGenerations, 4) : 0
        ];
    }

    /**
     * Ottiene cronologia test
     */
    private function getTestHistoryData(int $perPage, ?string $template = null): array
    {
        $query = \App\Models\PromptTest::query();

        if ($template) {
            $query->where('template_name', $template);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->toArray();
    }
}
