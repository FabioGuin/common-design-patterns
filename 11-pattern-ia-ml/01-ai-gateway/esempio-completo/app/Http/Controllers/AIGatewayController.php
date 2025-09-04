<?php

namespace App\Http\Controllers;

use App\Services\AI\AIGatewayService;
use App\Services\AI\RateLimiter;
use App\Services\AI\CacheService;
use App\Models\AIRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AIGatewayController extends Controller
{
    private AIGatewayService $aiService;
    private RateLimiter $rateLimiter;
    private CacheService $cacheService;

    public function __construct(
        AIGatewayService $aiService,
        RateLimiter $rateLimiter,
        CacheService $cacheService
    ) {
        $this->aiService = $aiService;
        $this->rateLimiter = $rateLimiter;
        $this->cacheService = $cacheService;
    }

    /**
     * Dashboard principale
     */
    public function dashboard(): View
    {
        $providerStatus = $this->aiService->getProviderStatus();
        $metrics = $this->aiService->getMetrics();
        $rateLimits = $this->rateLimiter->getAllLimits();
        $cacheStats = $this->cacheService->getStats();

        return view('ai-gateway.dashboard', compact(
            'providerStatus',
            'metrics',
            'rateLimits',
            'cacheStats'
        ));
    }

    /**
     * Genera testo
     */
    public function generateText(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:4000',
            'options' => 'sometimes|array',
            'options.max_tokens' => 'sometimes|integer|min:1|max:4000',
            'options.temperature' => 'sometimes|numeric|min:0|max:2',
            'options.model' => 'sometimes|string',
        ]);

        try {
            $result = $this->aiService->generateText(
                $request->input('prompt'),
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
            'options' => 'sometimes|array',
            'options.size' => 'sometimes|string|in:256x256,512x512,1024x1024',
            'options.n' => 'sometimes|integer|min:1|max:10',
        ]);

        try {
            $result = $this->aiService->generateImage(
                $request->input('prompt'),
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
            ], 500);
        }
    }

    /**
     * Traduci testo
     */
    public function translate(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:4000',
            'target_language' => 'required|string|max:10',
            'options' => 'sometimes|array',
        ]);

        try {
            $result = $this->aiService->translate(
                $request->input('text'),
                $request->input('target_language'),
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
            ], 500);
        }
    }

    /**
     * Stato dei provider
     */
    public function status(): JsonResponse
    {
        $providerStatus = $this->aiService->getProviderStatus();
        $rateLimits = $this->rateLimiter->getAllLimits();

        return response()->json([
            'success' => true,
            'data' => [
                'providers' => $providerStatus,
                'rate_limits' => $rateLimits
            ]
        ]);
    }

    /**
     * Metriche e statistiche
     */
    public function metrics(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $globalStats = AIRequest::getGlobalStats($startDate, $endDate);
        $providerStats = [];
        $dailyCosts = AIRequest::getDailyCosts($startDate, $endDate);
        $topPrompts = AIRequest::getTopPrompts(10, $startDate, $endDate);
        $providerPerformance = AIRequest::getProviderPerformance($startDate, $endDate);

        // Statistiche per ogni provider
        foreach ($globalStats['providers_used'] as $provider) {
            $providerStats[$provider] = AIRequest::getProviderStats($provider, $startDate, $endDate);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'global' => $globalStats,
                'providers' => $providerStats,
                'daily_costs' => $dailyCosts,
                'top_prompts' => $topPrompts,
                'performance' => $providerPerformance,
                'cache' => $this->cacheService->getStats()
            ]
        ]);
    }

    /**
     * Test dei provider
     */
    public function testProviders(): JsonResponse
    {
        $testPrompt = "Test di funzionamento del provider AI";
        $results = [];

        $providerStatus = $this->aiService->getProviderStatus();

        foreach ($providerStatus as $provider) {
            if (!$provider['available']) {
                $results[$provider['name']] = [
                    'success' => false,
                    'error' => 'Provider non disponibile'
                ];
                continue;
            }

            try {
                $startTime = microtime(true);
                $result = $this->aiService->generateText($testPrompt);
                $duration = microtime(true) - $startTime;

                $results[$provider['name']] = [
                    'success' => true,
                    'duration' => $duration,
                    'provider_used' => $result['provider'],
                    'cost' => $result['cost'],
                    'tokens_used' => $result['tokens_used']
                ];

            } catch (\Exception $e) {
                $results[$provider['name']] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * Gestione cache
     */
    public function cacheManagement(Request $request): JsonResponse
    {
        $action = $request->input('action');

        switch ($action) {
            case 'clear':
                $cleared = $this->cacheService->clear();
                return response()->json([
                    'success' => $cleared,
                    'message' => $cleared ? 'Cache pulita con successo' : 'Errore nella pulizia della cache'
                ]);

            case 'stats':
                $stats = $this->cacheService->getStats();
                return response()->json([
                    'success' => true,
                    'data' => $stats
                ]);

            case 'keys':
                $pattern = $request->input('pattern', '*');
                $keys = $this->cacheService->getKeysByPattern($pattern);
                return response()->json([
                    'success' => true,
                    'data' => $keys
                ]);

            case 'key_info':
                $key = $request->input('key');
                $info = $this->cacheService->getKeyInfo($key);
                return response()->json([
                    'success' => $info !== null,
                    'data' => $info
                ]);

            default:
                return response()->json([
                    'success' => false,
                    'error' => 'Azione non supportata'
                ], 400);
        }
    }

    /**
     * Reset rate limits
     */
    public function resetRateLimits(Request $request): JsonResponse
    {
        $provider = $request->input('provider');

        if ($provider) {
            $this->rateLimiter->resetLimits($provider);
            $message = "Rate limits resettati per provider {$provider}";
        } else {
            // Reset per tutti i provider
            $allLimits = $this->rateLimiter->getAllLimits();
            foreach (array_keys($allLimits) as $providerName) {
                $this->rateLimiter->resetLimits($providerName);
            }
            $message = "Rate limits resettati per tutti i provider";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Cronologia richieste
     */
    public function requestHistory(Request $request): JsonResponse
    {
        $query = AIRequest::query();

        // Filtri
        if ($request->has('provider')) {
            $query->byProvider($request->input('provider'));
        }

        if ($request->has('success')) {
            $query->where('success', $request->input('success'));
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->inPeriod($request->input('start_date'), $request->input('end_date'));
        }

        // Paginazione
        $perPage = $request->input('per_page', 20);
        $requests = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $requests
        ]);
    }
}
