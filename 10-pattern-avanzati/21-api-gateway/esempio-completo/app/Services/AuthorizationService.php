<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthorizationService
{
    protected $serviceId = 'authorization-service';
    protected $version = '1.0.0';

    /**
     * Autorizza una richiesta
     */
    public function authorize(Request $request, ?array $user): array
    {
        try {
            // Se non c'è utente, verifica se la risorsa è pubblica
            if (!$user) {
                return $this->authorizePublicResource($request);
            }

            // Verifica i permessi dell'utente
            $permission = $this->extractPermission($request);
            if (!$permission) {
                return [
                    'success' => false,
                    'error' => 'Permission not found'
                ];
            }

            // Verifica se l'utente ha il permesso
            if (!$this->hasPermission($user, $permission)) {
                return [
                    'success' => false,
                    'error' => 'Insufficient permissions'
                ];
            }

            // Verifica accesso alla risorsa specifica
            if (!$this->hasResourceAccess($user, $request)) {
                return [
                    'success' => false,
                    'error' => 'Resource access denied'
                ];
            }

            return [
                'success' => true,
                'permission' => $permission,
                'user' => $user
            ];

        } catch (\Exception $e) {
            Log::error("Authorization Service: Errore nell'autorizzazione", [
                'error' => $e->getMessage(),
                'request_path' => $request->path(),
                'user_id' => $user['id'] ?? null,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Authorization error'
            ];
        }
    }

    /**
     * Autorizza una risorsa pubblica
     */
    private function authorizePublicResource(Request $request): array
    {
        $publicResources = [
            'api/v1/gateway/health',
            'api/v1/gateway/stats',
            'api/v1/gateway/services'
        ];

        $path = $request->path();
        
        foreach ($publicResources as $publicResource) {
            if (str_starts_with($path, $publicResource)) {
                return [
                    'success' => true,
                    'permission' => 'public',
                    'user' => null
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Public resource not found'
        ];
    }

    /**
     * Estrae il permesso richiesto dalla richiesta
     */
    private function extractPermission(Request $request): ?string
    {
        $path = $request->path();
        $method = $request->method();

        // Rimuovi il prefisso /api/v1 se presente
        $path = preg_replace('/^api\/v1\//', '', $path);

        // Determina il servizio
        $pathParts = explode('/', $path);
        $service = $pathParts[0] ?? '';

        // Mappa i permessi
        $permissions = [
            'users' => [
                'GET' => 'users.read',
                'POST' => 'users.create',
                'PUT' => 'users.update',
                'DELETE' => 'users.delete'
            ],
            'products' => [
                'GET' => 'products.read',
                'POST' => 'products.create',
                'PUT' => 'products.update',
                'DELETE' => 'products.delete'
            ],
            'orders' => [
                'GET' => 'orders.read',
                'POST' => 'orders.create',
                'PUT' => 'orders.update',
                'DELETE' => 'orders.delete'
            ],
            'payments' => [
                'GET' => 'payments.read',
                'POST' => 'payments.create',
                'PUT' => 'payments.update',
                'DELETE' => 'payments.delete'
            ]
        ];

        return $permissions[$service][$method] ?? null;
    }

    /**
     * Verifica se l'utente ha il permesso
     */
    private function hasPermission(array $user, string $permission): bool
    {
        $userPermissions = $user['permissions'] ?? [];
        $userRole = $user['role'] ?? 'user';

        // Verifica permessi diretti
        if (in_array($permission, $userPermissions)) {
            return true;
        }

        // Verifica permessi basati su ruolo
        $rolePermissions = $this->getRolePermissions($userRole);
        if (in_array($permission, $rolePermissions)) {
            return true;
        }

        // Verifica permessi wildcard
        foreach ($userPermissions as $userPermission) {
            if ($this->matchWildcardPermission($userPermission, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ottiene i permessi per ruolo
     */
    private function getRolePermissions(string $role): array
    {
        $rolePermissions = [
            'admin' => [
                'users.read', 'users.create', 'users.update', 'users.delete',
                'products.read', 'products.create', 'products.update', 'products.delete',
                'orders.read', 'orders.create', 'orders.update', 'orders.delete',
                'payments.read', 'payments.create', 'payments.update', 'payments.delete'
            ],
            'user' => [
                'users.read', 'users.update',
                'products.read',
                'orders.read', 'orders.create', 'orders.update',
                'payments.read', 'payments.create'
            ],
            'guest' => [
                'products.read'
            ]
        ];

        return $rolePermissions[$role] ?? [];
    }

    /**
     * Verifica se un permesso wildcard corrisponde
     */
    private function matchWildcardPermission(string $wildcard, string $permission): bool
    {
        // Sostituisci * con .* per regex
        $pattern = str_replace('*', '.*', $wildcard);
        $pattern = '/^' . $pattern . '$/';

        return preg_match($pattern, $permission);
    }

    /**
     * Verifica l'accesso alla risorsa specifica
     */
    private function hasResourceAccess(array $user, Request $request): bool
    {
        $path = $request->path();
        $method = $request->method();

        // Verifica accesso a risorse specifiche
        if ($this->isOwnResource($user, $request)) {
            return true;
        }

        // Verifica accesso basato su ruolo
        if ($this->hasRoleAccess($user, $path, $method)) {
            return true;
        }

        return false;
    }

    /**
     * Verifica se l'utente sta accedendo alle proprie risorse
     */
    private function isOwnResource(array $user, Request $request): bool
    {
        $path = $request->path();
        $userId = $user['id'];

        // Verifica se sta accedendo ai propri dati
        if (str_contains($path, '/users/' . $userId)) {
            return true;
        }

        // Verifica se sta accedendo ai propri ordini
        if (str_contains($path, '/orders') && $request->has('user_id')) {
            return $request->get('user_id') === $userId;
        }

        return false;
    }

    /**
     * Verifica l'accesso basato su ruolo
     */
    private function hasRoleAccess(array $user, string $path, string $method): bool
    {
        $role = $user['role'] ?? 'user';

        // Admin ha accesso a tutto
        if ($role === 'admin') {
            return true;
        }

        // User ha accesso limitato
        if ($role === 'user') {
            $restrictedPaths = [
                'api/v1/users' => ['DELETE'],
                'api/v1/products' => ['POST', 'PUT', 'DELETE'],
                'api/v1/orders' => ['DELETE'],
                'api/v1/payments' => ['DELETE']
            ];

            foreach ($restrictedPaths as $restrictedPath => $restrictedMethods) {
                if (str_starts_with($path, $restrictedPath) && in_array($method, $restrictedMethods)) {
                    return false;
                }
            }

            return true;
        }

        // Guest ha accesso molto limitato
        if ($role === 'guest') {
            $allowedPaths = [
                'api/v1/products' => ['GET'],
                'api/v1/gateway/health' => ['GET'],
                'api/v1/gateway/stats' => ['GET']
            ];

            foreach ($allowedPaths as $allowedPath => $allowedMethods) {
                if (str_starts_with($path, $allowedPath) && in_array($method, $allowedMethods)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    /**
     * Ottiene i permessi di un utente
     */
    public function getUserPermissions(array $user): array
    {
        $directPermissions = $user['permissions'] ?? [];
        $rolePermissions = $this->getRolePermissions($user['role'] ?? 'user');

        return array_unique(array_merge($directPermissions, $rolePermissions));
    }

    /**
     * Verifica se un utente ha un ruolo specifico
     */
    public function hasRole(array $user, string $role): bool
    {
        return ($user['role'] ?? 'user') === $role;
    }

    /**
     * Verifica se un utente ha uno dei ruoli specificati
     */
    public function hasAnyRole(array $user, array $roles): bool
    {
        $userRole = $user['role'] ?? 'user';
        return in_array($userRole, $roles);
    }

    /**
     * Ottiene il livello di accesso di un utente
     */
    public function getAccessLevel(array $user): string
    {
        $role = $user['role'] ?? 'user';
        
        $accessLevels = [
            'admin' => 'full',
            'user' => 'limited',
            'guest' => 'readonly'
        ];

        return $accessLevels[$role] ?? 'none';
    }

    /**
     * Verifica se un utente può accedere a una risorsa
     */
    public function canAccessResource(array $user, string $resource, string $action): bool
    {
        $permission = $resource . '.' . $action;
        return $this->hasPermission($user, $permission);
    }

    /**
     * Ottiene le risorse accessibili per un utente
     */
    public function getAccessibleResources(array $user): array
    {
        $role = $user['role'] ?? 'user';
        
        $resources = [
            'admin' => [
                'users' => ['read', 'create', 'update', 'delete'],
                'products' => ['read', 'create', 'update', 'delete'],
                'orders' => ['read', 'create', 'update', 'delete'],
                'payments' => ['read', 'create', 'update', 'delete']
            ],
            'user' => [
                'users' => ['read', 'update'],
                'products' => ['read'],
                'orders' => ['read', 'create', 'update'],
                'payments' => ['read', 'create']
            ],
            'guest' => [
                'products' => ['read']
            ]
        ];

        return $resources[$role] ?? [];
    }

    /**
     * Health check del servizio
     */
    public function healthCheck(): array
    {
        try {
            return [
                'success' => true,
                'status' => 'healthy',
                'service' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'service' => $this->serviceId,
                'version' => $this->version,
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Ottiene l'ID del servizio
     */
    public function getId(): string
    {
        return $this->serviceId;
    }

    /**
     * Ottiene la versione del servizio
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}
