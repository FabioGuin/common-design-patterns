<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Verifica se l'utente è autenticato
        if (!Auth::check()) {
            return $this->unauthorizedResponse($request, 'User not authenticated');
        }

        $user = Auth::user();

        // Verifica se l'utente ha almeno uno dei ruoli richiesti
        if (!$this->userHasAnyRole($user, $roles)) {
            // Log tentativo di accesso non autorizzato
            Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_roles' => $user->roles->pluck('name')->toArray(),
                'required_roles' => $roles,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);

            return $this->unauthorizedResponse($request, 'Insufficient permissions');
        }

        // Log accesso autorizzato
        Log::info('Authorized access', [
            'user_id' => $user->id,
            'user_roles' => $user->roles->pluck('name')->toArray(),
            'required_roles' => $roles,
            'url' => $request->fullUrl(),
        ]);

        // Aggiungi informazioni di autorizzazione alla richiesta
        $request->merge([
            'authorized_roles' => $roles,
            'user_permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
        ]);

        return $next($request);
    }

    /**
     * Verifica se l'utente ha almeno uno dei ruoli richiesti
     */
    protected function userHasAnyRole($user, array $roles): bool
    {
        if (empty($roles)) {
            return true;
        }

        // Verifica se l'utente ha almeno uno dei ruoli
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Restituisci risposta di non autorizzazione
     */
    protected function unauthorizedResponse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'UNAUTHORIZED',
            ], 403);
        }

        abort(403, $message);
    }
}
