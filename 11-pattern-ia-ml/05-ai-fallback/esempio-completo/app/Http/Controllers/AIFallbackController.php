<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Services\AI\AIFallbackService;
use App\Models\FallbackLog;
use App\Models\AIProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AIFallbackController extends Controller
{
    private AIFallbackService $fallbackService;

    public function __construct(AIFallbackService $fallbackService)
    {
        $this->fallbackService = $fallbackService;
    }

    /**
     * Dashboard principale del fallback AI
     */
    public function dashboard(): View
    {
        $stats = $this->fallbackService->getFallbackStatistics();
        $providers = $this->fallbackService->getAvailableProviders();
        $strategies = $this->fallbackService->getAvailableStrategies();
        $realTimeMetrics = $this->fallbackService->getRealTimeMetrics();

        return view('ai-fallback.dashboard', compact(
            'stats',
            'providers',
            'strategies',
            'realTimeMetrics'
        ));
    }

    /**
     * Pagina gestione provider
     */
    public function providers(): View
    {
        $providers = $this->fallbackService->getAvailableProviders();
        $providerStats = AIProvider::getAggregateStats();
        $topPerformers = AIProvider::getTopPerformers();
        $problematicProviders = AIProvider::getProblematicProviders();

        return view('ai-fallback.providers', compact(
            'providers',
            'providerStats',
            'topPerformers',
            'problematicProviders'
        ));
    }

    /**
     * Pagina log fallback
     */
    public function logs(Request $request): View
    {
        $filters = [
            'provider' => $request->get('provider'),
            'strategy' => $request->get('strategy'),
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'limit' => $request->get('limit', 50)
        ];

        $logs = $this->fallbackService->getFallbackLogs($filters);
        $logStats = FallbackLog::getAggregateStats();
        $problematicRequests = FallbackLog::getProblematicRequests();

        return view('ai-fallback.logs', compact(
            'logs',
            'logStats',
            'problematicRequests',
            'filters'
        ));
    }

    /**
     * API: Genera testo con fallback
     */
    public function generate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|max:4000',
            'strategy' => 'string|in:provider_chain,cache_fallback,queue_fallback,static_fallback,hybrid_fallback',
            'max_tokens' => 'integer|min:1|max:4000',
            'temperature' => 'numeric|min:0|max:2',
            'simulate_provider_failure' => 'string',
            'simulate_intermittent_failure' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $options = $request->only([
                'strategy', 'max_tokens', 'temperature', 
                'simulate_provider_failure', 'simulate_intermittent_failure'
            ]);

            $response = $this->fallbackService->generateText(
                $request->input('prompt'),
                $options
            );

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Generation failed', [
                'prompt' => $request->input('prompt'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Text generation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Genera con strategia specifica
     */
    public function generateWithStrategy(Request $request, string $strategy): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|max:4000',
            'max_tokens' => 'integer|min:1|max:4000',
            'temperature' => 'numeric|min:0|max:2'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $options = $request->only(['max_tokens', 'temperature']);
            $response = $this->fallbackService->generateWithStrategy(
                $request->input('prompt'),
                $strategy,
                $options
            );

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Strategy generation failed', [
                'strategy' => $strategy,
                'prompt' => $request->input('prompt'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Strategy generation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Ottiene provider disponibili
     */
    public function getProviders(): JsonResponse
    {
        try {
            $providers = $this->fallbackService->getAvailableProviders();

            return response()->json([
                'success' => true,
                'providers' => $providers
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Failed to get providers', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get providers'
            ], 500);
        }
    }

    /**
     * API: Ottiene stato di salute dei provider
     */
    public function getHealth(): JsonResponse
    {
        try {
            $health = $this->fallbackService->getAllProvidersHealth();

            return response()->json([
                'success' => true,
                'health' => $health
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Failed to get health status', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get health status'
            ], 500);
        }
    }

    /**
     * API: Ottiene stato di salute di un provider specifico
     */
    public function getProviderHealth(string $provider): JsonResponse
    {
        try {
            $health = $this->fallbackService->getProviderHealth($provider);

            return response()->json([
                'success' => true,
                'provider' => $provider,
                'health' => $health
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Failed to get provider health', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get provider health'
            ], 500);
        }
    }

    /**
     * API: Retry di una richiesta
     */
    public function retry(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $response = $this->fallbackService->retryRequest($request->input('request_id'));

            return response()->json([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Retry failed', [
                'request_id' => $request->input('request_id'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Retry failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Ottiene log di fallback
     */
    public function getLogs(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'provider', 'strategy', 'status', 'date_from', 'date_to', 'limit'
            ]);

            $logs = $this->fallbackService->getFallbackLogs($filters);

            return response()->json([
                'success' => true,
                'logs' => $logs
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Failed to get logs', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get logs'
            ], 500);
        }
    }

    /**
     * API: Ottiene statistiche di fallback
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = $this->fallbackService->getFallbackStatistics();

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Failed to get statistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics'
            ], 500);
        }
    }

    /**
     * API: Ottiene metriche in tempo reale
     */
    public function getRealTimeMetrics(): JsonResponse
    {
        try {
            $metrics = $this->fallbackService->getRealTimeMetrics();

            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Failed to get real-time metrics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get real-time metrics'
            ], 500);
        }
    }

    /**
     * API: Ottiene stato circuit breaker
     */
    public function getCircuitBreakerState(string $provider): JsonResponse
    {
        try {
            $state = $this->fallbackService->getCircuitBreakerState($provider);

            return response()->json([
                'success' => true,
                'provider' => $provider,
                'state' => $state
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Failed to get circuit breaker state', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get circuit breaker state'
            ], 500);
        }
    }

    /**
     * API: Reset circuit breaker
     */
    public function resetCircuitBreaker(string $provider): JsonResponse
    {
        try {
            $success = $this->fallbackService->resetCircuitBreaker($provider);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Circuit breaker reset successfully' : 'Failed to reset circuit breaker'
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Failed to reset circuit breaker', [
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset circuit breaker'
            ], 500);
        }
    }

    /**
     * API: Abilita/disabilita provider
     */
    public function toggleProvider(Request $request, string $provider): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $success = $this->fallbackService->toggleProvider($provider, $request->input('enabled'));

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Provider toggled successfully' : 'Failed to toggle provider'
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Failed to toggle provider', [
                'provider' => $provider,
                'enabled' => $request->input('enabled'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle provider'
            ], 500);
        }
    }

    /**
     * API: Ottiene strategie disponibili
     */
    public function getStrategies(): JsonResponse
    {
        try {
            $strategies = $this->fallbackService->getAvailableStrategies();

            return response()->json([
                'success' => true,
                'strategies' => $strategies
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Failed to get strategies', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get strategies'
            ], 500);
        }
    }

    /**
     * API: Pulisce log vecchi
     */
    public function cleanupLogs(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'days' => 'integer|min:1|max:365'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $days = $request->input('days', 30);
            $deletedCount = $this->fallbackService->cleanupOldLogs($days);

            return response()->json([
                'success' => true,
                'deleted_count' => $deletedCount,
                'message' => "Cleaned up {$deletedCount} old log entries"
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Failed to cleanup logs', [
                'days' => $request->input('days', 30),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup logs'
            ], 500);
        }
    }

    /**
     * API: Test di fallback
     */
    public function testFallback(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|max:1000',
            'test_type' => 'string|in:provider_failure,circuit_breaker,retry,full_fallback'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $prompt = $request->input('prompt');
            $testType = $request->input('test_type', 'provider_failure');

            $options = [
                'simulate_provider_failure' => $testType === 'provider_failure' ? 'openai' : null,
                'simulate_intermittent_failure' => $testType === 'retry',
                'strategy' => 'provider_chain'
            ];

            $response = $this->fallbackService->generateText($prompt, $options);

            return response()->json([
                'success' => true,
                'test_type' => $testType,
                'response' => $response
            ]);

        } catch (\Exception $e) {
            Log::error('AI Fallback: Test failed', [
                'test_type' => $request->input('test_type'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
