<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class UserService
{
    protected $serviceId = 'user-service';
    protected $version = '1.0.0';

    /**
     * Crea un nuovo utente
     */
    public function createUser(array $userData): array
    {
        try {
            // Valida i dati dell'utente
            $this->validateUserData($userData);

            // Crea l'utente
            $user = new User([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'phone' => $userData['phone'] ?? null,
                'address' => $userData['address'] ?? null,
                'status' => 'active'
            ]);

            $user->save();

            // Cache dell'utente
            Cache::put("user:{$user->id}", $user, 3600);

            Log::info("User Service: Utente creato", [
                'user_id' => $user->id,
                'email' => $user->email,
                'service' => $this->serviceId
            ]);

            return [
                'success' => true,
                'data' => $user->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("User Service: Errore nella creazione utente", [
                'error' => $e->getMessage(),
                'user_data' => $userData,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene un utente per ID
     */
    public function getUser(string $userId): array
    {
        try {
            // Prova prima la cache
            $cachedUser = Cache::get("user:{$userId}");
            if ($cachedUser) {
                return [
                    'success' => true,
                    'data' => $cachedUser->toArray(),
                    'service' => $this->serviceId,
                    'cached' => true
                ];
            }

            // Recupera dal database
            $user = User::find($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Utente non trovato',
                    'service' => $this->serviceId
                ];
            }

            // Cache dell'utente
            Cache::put("user:{$userId}", $user, 3600);

            return [
                'success' => true,
                'data' => $user->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("User Service: Errore nel recupero utente", [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene un utente per email
     */
    public function getUserByEmail(string $email): array
    {
        try {
            $user = User::where('email', $email)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Utente non trovato',
                    'service' => $this->serviceId
                ];
            }

            return [
                'success' => true,
                'data' => $user->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("User Service: Errore nel recupero utente per email", [
                'error' => $e->getMessage(),
                'email' => $email,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Aggiorna un utente
     */
    public function updateUser(string $userId, array $updateData): array
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Utente non trovato',
                    'service' => $this->serviceId
                ];
            }

            // Aggiorna i campi
            foreach ($updateData as $field => $value) {
                if (in_array($field, ['name', 'email', 'phone', 'address', 'status'])) {
                    $user->$field = $value;
                }
            }

            $user->save();

            // Aggiorna la cache
            Cache::put("user:{$userId}", $user, 3600);

            Log::info("User Service: Utente aggiornato", [
                'user_id' => $userId,
                'updated_fields' => array_keys($updateData),
                'service' => $this->serviceId
            ]);

            return [
                'success' => true,
                'data' => $user->toArray(),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("User Service: Errore nell'aggiornamento utente", [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Elimina un utente
     */
    public function deleteUser(string $userId): array
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Utente non trovato',
                    'service' => $this->serviceId
                ];
            }

            $user->delete();

            // Rimuovi dalla cache
            Cache::forget("user:{$userId}");

            Log::info("User Service: Utente eliminato", [
                'user_id' => $userId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => true,
                'message' => 'Utente eliminato con successo',
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("User Service: Errore nell'eliminazione utente", [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Lista tutti gli utenti
     */
    public function listUsers(int $limit = 100, int $offset = 0): array
    {
        try {
            $users = User::limit($limit)->offset($offset)->get();
            $usersArray = $users->map(function($user) {
                return $user->toArray();
            })->toArray();

            return [
                'success' => true,
                'data' => $usersArray,
                'count' => count($usersArray),
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("User Service: Errore nel recupero lista utenti", [
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Autentica un utente
     */
    public function authenticateUser(string $email, string $password): array
    {
        try {
            $user = User::where('email', $email)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'error' => 'Credenziali non valide',
                    'service' => $this->serviceId
                ];
            }

            if (!Hash::check($password, $user->password)) {
                return [
                    'success' => false,
                    'error' => 'Credenziali non valide',
                    'service' => $this->serviceId
                ];
            }

            // Genera token di sessione
            $token = $this->generateSessionToken($user);

            return [
                'success' => true,
                'data' => [
                    'user' => $user->toArray(),
                    'token' => $token
                ],
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("User Service: Errore nell'autenticazione", [
                'error' => $e->getMessage(),
                'email' => $email,
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Ottiene statistiche degli utenti
     */
    public function getUserStats(): array
    {
        try {
            $totalUsers = User::count();
            $activeUsers = User::where('status', 'active')->count();
            $inactiveUsers = User::where('status', 'inactive')->count();

            return [
                'success' => true,
                'data' => [
                    'total_users' => $totalUsers,
                    'active_users' => $activeUsers,
                    'inactive_users' => $inactiveUsers
                ],
                'service' => $this->serviceId
            ];

        } catch (\Exception $e) {
            Log::error("User Service: Errore nel recupero statistiche", [
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'service' => $this->serviceId
            ];
        }
    }

    /**
     * Health check del servizio
     */
    public function healthCheck(): array
    {
        try {
            // Verifica connessione database
            User::count();

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
     * Valida i dati dell'utente
     */
    private function validateUserData(array $userData): void
    {
        $required = ['name', 'email', 'password'];
        
        foreach ($required as $field) {
            if (!isset($userData[$field]) || empty($userData[$field])) {
                throw new \InvalidArgumentException("Campo obbligatorio mancante: {$field}");
            }
        }

        // Valida email
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email non valida");
        }

        // Verifica se l'email esiste già
        if (User::where('email', $userData['email'])->exists()) {
            throw new \InvalidArgumentException("Email già esistente");
        }
    }

    /**
     * Genera un token di sessione
     */
    private function generateSessionToken(User $user): string
    {
        return 'user_token_' . $user->id . '_' . time() . '_' . uniqid();
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
