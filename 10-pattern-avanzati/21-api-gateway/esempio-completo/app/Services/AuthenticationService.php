<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class AuthenticationService
{
    protected $serviceId = 'authentication-service';
    protected $version = '1.0.0';

    /**
     * Autentica una richiesta
     */
    public function authenticate(Request $request): array
    {
        try {
            // Verifica se la richiesta richiede autenticazione
            if (!$this->requiresAuthentication($request)) {
                return [
                    'success' => true,
                    'user' => null,
                    'method' => 'none'
                ];
            }

            // Prova diversi metodi di autenticazione
            $authMethods = [
                'jwt' => [$this, 'authenticateJwt'],
                'api_key' => [$this, 'authenticateApiKey'],
                'basic' => [$this, 'authenticateBasic'],
                'session' => [$this, 'authenticateSession']
            ];

            foreach ($authMethods as $method => $authFunction) {
                $result = call_user_func($authFunction, $request);
                if ($result['success']) {
                    return [
                        'success' => true,
                        'user' => $result['user'],
                        'method' => $method,
                        'token' => $result['token'] ?? null
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Authentication failed',
                'method' => 'none'
            ];

        } catch (\Exception $e) {
            Log::error("Authentication Service: Errore nell'autenticazione", [
                'error' => $e->getMessage(),
                'request_path' => $request->path(),
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => 'Authentication error',
                'method' => 'none'
            ];
        }
    }

    /**
     * Verifica se la richiesta richiede autenticazione
     */
    private function requiresAuthentication(Request $request): bool
    {
        $publicPaths = [
            'api/v1/gateway/health',
            'api/v1/gateway/stats',
            'api/v1/gateway/services'
        ];

        $path = $request->path();
        
        foreach ($publicPaths as $publicPath) {
            if (str_starts_with($path, $publicPath)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Autenticazione JWT
     */
    private function authenticateJwt(Request $request): array
    {
        try {
            $token = $this->extractJwtToken($request);
            if (!$token) {
                return ['success' => false];
            }

            // Simula verifica JWT
            $payload = $this->verifyJwtToken($token);
            if (!$payload) {
                return ['success' => false];
            }

            $user = $this->getUserById($payload['user_id']);
            if (!$user) {
                return ['success' => false];
            }

            return [
                'success' => true,
                'user' => $user,
                'token' => $token
            ];

        } catch (\Exception $e) {
            return ['success' => false];
        }
    }

    /**
     * Autenticazione API Key
     */
    private function authenticateApiKey(Request $request): array
    {
        try {
            $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');
            if (!$apiKey) {
                return ['success' => false];
            }

            // Verifica API key nel database
            $user = $this->getUserByApiKey($apiKey);
            if (!$user) {
                return ['success' => false];
            }

            return [
                'success' => true,
                'user' => $user
            ];

        } catch (\Exception $e) {
            return ['success' => false];
        }
    }

    /**
     * Autenticazione Basic
     */
    private function authenticateBasic(Request $request): array
    {
        try {
            $authHeader = $request->header('Authorization');
            if (!$authHeader || !str_starts_with($authHeader, 'Basic ')) {
                return ['success' => false];
            }

            $credentials = base64_decode(substr($authHeader, 6));
            $credentials = explode(':', $credentials, 2);
            
            if (count($credentials) !== 2) {
                return ['success' => false];
            }

            [$email, $password] = $credentials;
            $user = $this->getUserByEmail($email);
            
            if (!$user || !Hash::check($password, $user['password'])) {
                return ['success' => false];
            }

            return [
                'success' => true,
                'user' => $user
            ];

        } catch (\Exception $e) {
            return ['success' => false];
        }
    }

    /**
     * Autenticazione Session
     */
    private function authenticateSession(Request $request): array
    {
        try {
            $sessionId = $request->cookie('session_id') ?? $request->header('X-Session-ID');
            if (!$sessionId) {
                return ['success' => false];
            }

            $user = $this->getUserBySession($sessionId);
            if (!$user) {
                return ['success' => false];
            }

            return [
                'success' => true,
                'user' => $user
            ];

        } catch (\Exception $e) {
            return ['success' => false];
        }
    }

    /**
     * Estrae il token JWT dalla richiesta
     */
    private function extractJwtToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        return $request->query('token');
    }

    /**
     * Verifica un token JWT
     */
    private function verifyJwtToken(string $token): ?array
    {
        try {
            // Simula verifica JWT
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode($parts[1]), true);
            if (!$payload || !isset($payload['user_id'])) {
                return null;
            }

            // Verifica scadenza
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return null;
            }

            return $payload;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Ottiene un utente per ID
     */
    private function getUserById(string $userId): ?array
    {
        // Simula recupero utente dal database
        $users = [
            'user_123' => [
                'id' => 'user_123',
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'user',
                'permissions' => ['read', 'write']
            ],
            'user_456' => [
                'id' => 'user_456',
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'permissions' => ['read', 'write', 'delete', 'admin']
            ]
        ];

        return $users[$userId] ?? null;
    }

    /**
     * Ottiene un utente per API key
     */
    private function getUserByApiKey(string $apiKey): ?array
    {
        // Simula recupero utente per API key
        $apiKeys = [
            'api_key_123' => 'user_123',
            'api_key_456' => 'user_456'
        ];

        $userId = $apiKeys[$apiKey] ?? null;
        if (!$userId) {
            return null;
        }

        return $this->getUserById($userId);
    }

    /**
     * Ottiene un utente per email
     */
    private function getUserByEmail(string $email): ?array
    {
        // Simula recupero utente per email
        $emails = [
            'test@example.com' => 'user_123',
            'admin@example.com' => 'user_456'
        ];

        $userId = $emails[$email] ?? null;
        if (!$userId) {
            return null;
        }

        return $this->getUserById($userId);
    }

    /**
     * Ottiene un utente per session
     */
    private function getUserBySession(string $sessionId): ?array
    {
        // Simula recupero utente per session
        $sessions = [
            'session_123' => 'user_123',
            'session_456' => 'user_456'
        ];

        $userId = $sessions[$sessionId] ?? null;
        if (!$userId) {
            return null;
        }

        return $this->getUserById($userId);
    }

    /**
     * Genera un token JWT
     */
    public function generateJwtToken(array $user): string
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'exp' => time() + 3600 // 1 ora
        ]));
        $signature = base64_encode(hash_hmac('sha256', $header . '.' . $payload, 'secret_key', true));

        return $header . '.' . $payload . '.' . $signature;
    }

    /**
     * Genera un API key
     */
    public function generateApiKey(array $user): string
    {
        return 'api_key_' . $user['id'] . '_' . uniqid();
    }

    /**
     * Genera un session ID
     */
    public function generateSessionId(array $user): string
    {
        return 'session_' . $user['id'] . '_' . uniqid();
    }

    /**
     * Valida le credenziali
     */
    public function validateCredentials(string $email, string $password): array
    {
        try {
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'User not found'
                ];
            }

            // Simula verifica password
            if ($password !== 'password123') {
                return [
                    'success' => false,
                    'error' => 'Invalid password'
                ];
            }

            return [
                'success' => true,
                'user' => $user
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Validation error'
            ];
        }
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
