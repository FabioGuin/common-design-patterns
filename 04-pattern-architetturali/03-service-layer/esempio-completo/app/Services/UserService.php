<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private NotificationService $notificationService,
        private ValidationService $validationService
    ) {}

    /**
     * Crea un nuovo utente
     */
    public function createUser(array $data): User
    {
        // Validazione business
        $this->validationService->validateUserData($data);
        
        // Processamento dati
        $processedData = $this->processUserData($data);
        
        // Creazione utente
        $user = $this->userRepository->create($processedData);
        
        // Azioni post-creazione
        $this->notificationService->notifyUserCreated($user);
        
        return $user;
    }

    /**
     * Crea un utente con profilo completo
     */
    public function createUserWithProfile(array $userData, array $profileData = []): User
    {
        DB::beginTransaction();
        
        try {
            // Crea utente
            $user = $this->createUser($userData);
            
            // Crea profilo se fornito
            if (!empty($profileData)) {
                $this->createUserProfile($user, $profileData);
            }
            
            // Invia email di benvenuto
            $this->notificationService->sendWelcomeEmail($user);
            
            // Notifica amministratori
            $this->notificationService->notifyUserCreated($user);
            
            DB::commit();
            
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Aggiorna un utente esistente
     */
    public function updateUser(int $id, array $data): User
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new \Exception('Utente non trovato');
        }

        // Validazione business
        $this->validationService->validateUserData($data, $id);
        
        // Processamento dati
        $processedData = $this->processUserData($data, $id);
        
        // Aggiornamento utente
        $this->userRepository->update($id, $processedData);
        
        // Azioni post-aggiornamento
        $this->notificationService->notifyUserUpdated($user);
        
        return $user->fresh();
    }

    /**
     * Attiva un utente
     */
    public function activateUser(int $id): User
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new \Exception('Utente non trovato');
        }

        // Attivazione
        $this->userRepository->activate($id);
        
        // Azioni post-attivazione
        $this->notificationService->notifyUserActivated($user);
        
        return $user->fresh();
    }

    /**
     * Disattiva un utente
     */
    public function deactivateUser(int $id): User
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new \Exception('Utente non trovato');
        }

        // Disattivazione
        $this->userRepository->deactivate($id);
        
        // Azioni post-disattivazione
        $this->notificationService->notifyUserDeactivated($user);
        
        return $user->fresh();
    }

    /**
     * Cambia il ruolo di un utente
     */
    public function changeUserRole(int $id, string $role): User
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new \Exception('Utente non trovato');
        }

        // Validazione ruolo
        if (!in_array($role, ['user', 'editor', 'admin'])) {
            throw new \Exception('Ruolo non valido');
        }

        // Cambio ruolo
        $this->userRepository->changeRole($id, $role);
        
        // Azioni post-cambio ruolo
        $this->notificationService->notifyUserRoleChanged($user, $role);
        
        return $user->fresh();
    }

    /**
     * Elimina un utente
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new \Exception('Utente non trovato');
        }

        // Azioni pre-eliminazione
        $this->notificationService->notifyUserDeleted($user);
        
        // Eliminazione
        return $this->userRepository->delete($id);
    }

    /**
     * Recupera utenti con filtri
     */
    public function getUsers(array $filters = []): Collection
    {
        $query = $this->userRepository->findAll();

        // Applica filtri
        if (isset($filters['role'])) {
            $query = $query->where('role', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query = $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $query = $this->userRepository->search($filters['search']);
        }

        return $query;
    }

    /**
     * Recupera un utente per ID
     */
    public function getUserById(int $id): User
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new \Exception('Utente non trovato');
        }

        return $user;
    }

    /**
     * Recupera utenti attivi
     */
    public function getActiveUsers(): Collection
    {
        return $this->userRepository->findActive();
    }

    /**
     * Recupera utenti per ruolo
     */
    public function getUsersByRole(string $role): Collection
    {
        return $this->userRepository->findByRole($role);
    }

    /**
     * Recupera utenti più attivi
     */
    public function getMostActiveUsers(int $limit = 10): Collection
    {
        return $this->userRepository->findMostActive($limit);
    }

    /**
     * Processa i dati dell'utente
     */
    private function processUserData(array $data, ?int $userId = null): array
    {
        // Hash della password se fornita
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Rimuove la password se vuota (per aggiornamenti)
            unset($data['password']);
        }

        // Normalizza l'email
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        // Normalizza il nome
        if (isset($data['name'])) {
            $data['name'] = trim($data['name']);
        }

        // Imposta valori di default
        if (!isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        if (!isset($data['role'])) {
            $data['role'] = 'user';
        }

        return $data;
    }

    /**
     * Crea un profilo utente
     */
    private function createUserProfile(User $user, array $profileData): void
    {
        // Logica per creare profilo utente
        // Questo è un esempio semplificato
        $user->update([
            'bio' => $profileData['bio'] ?? null,
            'avatar' => $profileData['avatar'] ?? null,
        ]);
    }

    /**
     * Recupera statistiche degli utenti
     */
    public function getUserStats(): array
    {
        return [
            'total' => $this->userRepository->count(),
            'active' => $this->userRepository->countActive(),
            'users' => $this->userRepository->countByRole('user'),
            'editors' => $this->userRepository->countByRole('editor'),
            'admins' => $this->userRepository->countByRole('admin'),
        ];
    }

    /**
     * Recupera statistiche dettagliate per un utente
     */
    public function getUserDetailedStats(int $userId): array
    {
        $user = $this->getUserById($userId);

        return [
            'user' => $user,
            'total_articles' => $user->articles()->count(),
            'published_articles' => $user->articles()->where('status', 'published')->count(),
            'draft_articles' => $user->articles()->where('status', 'draft')->count(),
            'member_since' => $user->created_at->diffForHumans(),
            'last_activity' => $user->updated_at->diffForHumans(),
        ];
    }
}
