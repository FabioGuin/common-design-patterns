<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuthenticationService;
use Illuminate\Support\Facades\Log;

class JwtAuthMiddleware
{
    protected $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $authResult = $this->authService->authenticate($request);

            if (!$authResult['success']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Authentication failed',
                    'message' => $authResult['error'] ?? 'Invalid credentials'
                ], 401);
            }

            // Aggiungi l'utente alla richiesta
            $request->merge(['user' => $authResult['user']]);

            $response = $next($request);

            // Aggiungi header di autenticazione
            $response->header('X-Auth-Method', $authResult['method'] ?? 'none');
            $response->header('X-User-ID', $authResult['user']['id'] ?? 'anonymous');

            return $response;

        } catch (\Exception $e) {
            Log::error("JWT Auth Middleware: Errore nell'autenticazione", [
                'error' => $e->getMessage(),
                'request_path' => $request->path()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Authentication error',
                'message' => 'An error occurred during authentication'
            ], 500);
        }
    }
}
