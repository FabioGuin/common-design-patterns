<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ApiGatewayService;
use App\Services\AuthenticationService;
use App\Services\AuthorizationService;
use App\Services\RateLimitService;
use App\Services\LoggingService;
use App\Services\CachingService;
use App\Services\MonitoringService;
use Illuminate\Support\Facades\Log;

class ApiGatewayMiddleware
{
    protected $apiGateway;
    protected $authService;
    protected $authorizationService;
    protected $rateLimitService;
    protected $loggingService;
    protected $cachingService;
    protected $monitoringService;

    public function __construct(
        ApiGatewayService $apiGateway,
        AuthenticationService $authService,
        AuthorizationService $authorizationService,
        RateLimitService $rateLimitService,
        LoggingService $loggingService,
        CachingService $cachingService,
        MonitoringService $monitoringService
    ) {
        $this->apiGateway = $apiGateway;
        $this->authService = $authService;
        $this->authorizationService = $authorizationService;
        $this->rateLimitService = $rateLimitService;
        $this->loggingService = $loggingService;
        $this->cachingService = $cachingService;
        $this->monitoringService = $monitoringService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $requestId = uniqid('req_');

        try {
            // 1. Logging della richiesta
            $this->loggingService->logRequest($request, $requestId);

            // 2. Autenticazione
            $authResult = $this->authService->authenticate($request);
            if (!$authResult['success']) {
                return $this->createErrorResponse('Unauthorized', 401, $requestId);
            }

            // 3. Autorizzazione
            $authorizationResult = $this->authorizationService->authorize($request, $authResult['user']);
            if (!$authorizationResult['success']) {
                return $this->createErrorResponse('Forbidden', 403, $requestId);
            }

            // 4. Rate Limiting
            $rateLimitResult = $this->rateLimitService->checkRateLimit($request, $authResult['user']);
            if (!$rateLimitResult['success']) {
                return $this->createErrorResponse('Too Many Requests', 429, $requestId);
            }

            // 5. Caching per richieste GET
            if ($request->isMethod('GET')) {
                $cacheKey = $this->generateCacheKey($request);
                $cachedResponse = $this->cachingService->get($cacheKey);
                if ($cachedResponse) {
                    return $this->createCachedResponse($cachedResponse, $requestId);
                }
            }

            // 6. Gestisci la richiesta
            $response = $next($request);

            // 7. Caching della risposta per richieste GET
            if ($request->isMethod('GET') && $response->getStatusCode() === 200) {
                $cacheKey = $this->generateCacheKey($request);
                $this->cachingService->put($cacheKey, $response->getContent(), 300); // 5 minuti
            }

            // 8. Monitoring
            $responseTime = microtime(true) - $startTime;
            $this->monitoringService->recordMetrics($request, [
                'success' => $response->getStatusCode() < 400,
                'status' => $response->getStatusCode(),
                'response_time' => $responseTime
            ], $responseTime);

            // 9. Logging della risposta
            $this->loggingService->logResponse($request, [
                'success' => $response->getStatusCode() < 400,
                'status' => $response->getStatusCode(),
                'response_time' => $responseTime
            ], $requestId);

            // 10. Aggiungi header di risposta
            $response->header('X-Request-ID', $requestId);
            $response->header('X-Response-Time', $responseTime);
            $response->header('X-Rate-Limit-Limit', $rateLimitResult['limit'] ?? 100);
            $response->header('X-Rate-Limit-Remaining', $rateLimitResult['remaining'] ?? 99);
            $response->header('X-Rate-Limit-Reset', $rateLimitResult['reset_time'] ?? now()->addMinute()->toISOString());

            return $response;

        } catch (\Exception $e) {
            Log::error("API Gateway Middleware: Errore nella gestione richiesta", [
                'error' => $e->getMessage(),
                'request_id' => $requestId,
                'request_path' => $request->path()
            ]);

            return $this->createErrorResponse('Internal Server Error', 500, $requestId);
        }
    }

    /**
     * Crea una risposta di errore
     */
    private function createErrorResponse(string $message, int $status, string $requestId)
    {
        return response()->json([
            'success' => false,
            'error' => $message,
            'status' => $status,
            'request_id' => $requestId,
            'gateway' => 'api-gateway'
        ], $status);
    }

    /**
     * Crea una risposta dalla cache
     */
    private function createCachedResponse($cachedData, string $requestId)
    {
        $response = response()->json($cachedData);
        $response->header('X-Request-ID', $requestId);
        $response->header('X-Cached', 'true');
        $response->header('X-Cache-Timestamp', now()->toISOString());

        return $response;
    }

    /**
     * Genera una chiave di cache per la richiesta
     */
    private function generateCacheKey(Request $request): string
    {
        $path = $request->path();
        $query = $request->query->all();
        $user = $request->user();

        $key = $path . ':' . md5(serialize($query));
        if ($user) {
            $key .= ':' . $user['id'];
        }

        return 'api_gateway:' . $key;
    }
}
