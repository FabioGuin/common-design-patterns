<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        // Verifica se l'utente è autenticato
        if (!Auth::guard($guard)->check()) {
            // Log tentativo di accesso non autorizzato
            Log::warning('Unauthenticated access attempt', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'guard' => $guard,
            ]);

            // Se è una richiesta API, restituisci JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'error_code' => 'UNAUTHENTICATED',
                ], 401);
            }

            // Per richieste web, redirect al login
            return redirect()->guest(route('login'));
        }

        // Log accesso autenticato
        Log::info('Authenticated access', [
            'user_id' => Auth::guard($guard)->id(),
            'url' => $request->fullUrl(),
            'guard' => $guard,
        ]);

        // Aggiungi informazioni utente alla richiesta
        $request->merge([
            'authenticated_user' => Auth::guard($guard)->user(),
        ]);

        return $next($request);
    }
}
