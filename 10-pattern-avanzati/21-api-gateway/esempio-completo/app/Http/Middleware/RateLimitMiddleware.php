<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\RateLimitService;
use Illuminate\Support\Facades\Log;

class RateLimitMiddleware
{
    protected $rateLimitService;

    public function __construct(RateLimitService $rateLimitService)
    {
        $this->rateLimitService = $rateLimitService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = $request->user();
            $rateLimitResult = $this->rateLimitService->checkRateLimit($request, $user);

            if (!$rateLimitResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Rate limit exceeded',
                    'limit' => $rateLimitResult['limit'] ?? 100,
                    'remaining' => $rateLimitResult['remaining'] ?? 0,
                    'reset_time' => $rateLimitResult['reset_time'] ?? now()->addMinute()->toISOString()
                ], 429);
            }

            $response = $next($request);

            // Aggiungi header di rate limiting
            $response->header('X-Rate-Limit-Limit', $rateLimitResult['limit'] ?? 100);
            $response->header('X-Rate-Limit-Remaining', $rateLimitResult['remaining'] ?? 99);
            $response->header('X-Rate-Limit-Reset', $rateLimitResult['reset_time'] ?? now()->addMinute()->toISOString());

            return $response;

        } catch (\Exception $e) {
            Log::error("Rate Limit Middleware: Errore nel controllo rate limit", [
                'error' => $e->getMessage(),
                'request_path' => $request->path()
            ]);

            // In caso di errore, permette la richiesta
            return $next($request);
        }
    }
}
