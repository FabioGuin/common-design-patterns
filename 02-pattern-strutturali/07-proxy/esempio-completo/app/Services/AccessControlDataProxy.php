<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AccessControlDataProxy implements DataServiceInterface
{
    private DataServiceInterface $dataService;
    private array $userRoles;
    
    public function __construct(DataServiceInterface $dataService)
    {
        $this->dataService = $dataService;
        $this->userRoles = [
            'admin' => ['read', 'write', 'delete'],
            'moderator' => ['read', 'write'],
            'user' => ['read'],
            'guest' => []
        ];
    }
    
    /**
     * Ottiene i dati di un utente specifico con controllo di accesso
     */
    public function getUserData(int $userId): array
    {
        $currentUserRole = $this->getCurrentUserRole();
        
        if (!$this->hasPermission($currentUserRole, 'read')) {
            Log::warning("AccessControlDataProxy: Access denied for user role '{$currentUserRole}' to getUserData");
            throw new \Exception("Access denied: Insufficient permissions to read user data");
        }
        
        Log::info("AccessControlDataProxy: Access granted for user role '{$currentUserRole}' to getUserData");
        return $this->dataService->getUserData($userId);
    }
    
    /**
     * Ottiene tutti gli utenti con controllo di accesso
     */
    public function getAllUsers(): array
    {
        $currentUserRole = $this->getCurrentUserRole();
        
        if (!$this->hasPermission($currentUserRole, 'read')) {
            Log::warning("AccessControlDataProxy: Access denied for user role '{$currentUserRole}' to getAllUsers");
            throw new \Exception("Access denied: Insufficient permissions to read all users");
        }
        
        Log::info("AccessControlDataProxy: Access granted for user role '{$currentUserRole}' to getAllUsers");
        return $this->dataService->getAllUsers();
    }
    
    /**
     * Ottiene i post di un utente con controllo di accesso
     */
    public function getUserPosts(int $userId): array
    {
        $currentUserRole = $this->getCurrentUserRole();
        
        if (!$this->hasPermission($currentUserRole, 'read')) {
            Log::warning("AccessControlDataProxy: Access denied for user role '{$currentUserRole}' to getUserPosts");
            throw new \Exception("Access denied: Insufficient permissions to read user posts");
        }
        
        Log::info("AccessControlDataProxy: Access granted for user role '{$currentUserRole}' to getUserPosts");
        return $this->dataService->getUserPosts($userId);
    }
    
    /**
     * Ottiene i commenti di un post con controllo di accesso
     */
    public function getPostComments(int $postId): array
    {
        $currentUserRole = $this->getCurrentUserRole();
        
        if (!$this->hasPermission($currentUserRole, 'read')) {
            Log::warning("AccessControlDataProxy: Access denied for user role '{$currentUserRole}' to getPostComments");
            throw new \Exception("Access denied: Insufficient permissions to read post comments");
        }
        
        Log::info("AccessControlDataProxy: Access granted for user role '{$currentUserRole}' to getPostComments");
        return $this->dataService->getPostComments($postId);
    }
    
    /**
     * Verifica se l'utente corrente ha il permesso specificato
     */
    private function hasPermission(string $userRole, string $permission): bool
    {
        $permissions = $this->userRoles[$userRole] ?? [];
        return in_array($permission, $permissions);
    }
    
    /**
     * Ottiene il ruolo dell'utente corrente (simulato)
     * In un'applicazione reale, questo verrebbe dal sistema di autenticazione
     */
    private function getCurrentUserRole(): string
    {
        // Simulazione: in un'applicazione reale, questo verrebbe da session/auth
        return request()->get('user_role', 'guest');
    }
    
    /**
     * Imposta il ruolo dell'utente per i test
     */
    public function setUserRole(string $role): void
    {
        request()->merge(['user_role' => $role]);
    }
}
