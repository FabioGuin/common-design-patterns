<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Recupera tutti gli utenti
     */
    public function getAllUsers(): Collection
    {
        return $this->userRepository->findAll();
    }

    /**
     * Recupera utenti attivi
     */
    public function getActiveUsers(): Collection
    {
        return $this->userRepository->findActive();
    }

    /**
     * Recupera utenti inattivi
     */
    public function getInactiveUsers(): Collection
    {
        return $this->userRepository->findInactive();
    }

    /**
     * Recupera un utente per ID
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Recupera un utente per email
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Recupera utenti per ruolo
     */
    public function getUsersByRole(string $role): Collection
    {
        return $this->userRepository->findByRole($role);
    }

    /**
     * Cerca utenti per termine
     */
    public function searchUsers(string $term): Collection
    {
        if (empty(trim($term))) {
            return collect();
        }

        return $this->userRepository->search($term);
    }

    /**
     * Recupera utenti con articoli
     */
    public function getUsersWithArticles(): Collection
    {
        return $this->userRepository->findWithArticles();
    }

    /**
     * Recupera utenti senza articoli
     */
    public function getUsersWithoutArticles(): Collection
    {
        return $this->userRepository->findWithoutArticles();
    }

    /**
     * Recupera utenti più attivi
     */
    public function getMostActiveUsers(int $limit = 10): Collection
    {
        return $this->userRepository->findMostActive($limit);
    }

    /**
     * Recupera utenti recenti
     */
    public function getRecentUsers(int $limit = 10): Collection
    {
        return $this->userRepository->findRecent($limit);
    }

    /**
     * Crea un nuovo utente
     */
    public function createUser(array $data): User
    {
        // Logica di business per la creazione
        $data = $this->prepareUserData($data);

        return $this->userRepository->create($data);
    }

    /**
     * Aggiorna un utente esistente
     */
    public function updateUser(int $id, array $data): bool
    {
        // Logica di business per l'aggiornamento
        $data = $this->prepareUserData($data, $id);

        return $this->userRepository->update($id, $data);
    }

    /**
     * Elimina un utente
     */
    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    /**
     * Attiva un utente
     */
    public function activateUser(int $id): bool
    {
        return $this->userRepository->activate($id);
    }

    /**
     * Disattiva un utente
     */
    public function deactivateUser(int $id): bool
    {
        return $this->userRepository->deactivate($id);
    }

    /**
     * Cambia il ruolo di un utente
     */
    public function changeUserRole(int $id, string $role): bool
    {
        if (!in_array($role, ['user', 'editor', 'admin'])) {
            return false;
        }

        return $this->userRepository->changeRole($id, $role);
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
     * Recupera utenti con paginazione
     */
    public function getPaginatedUsers(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    /**
     * Recupera utenti con statistiche
     */
    public function getUsersWithStats(): Collection
    {
        return $this->userRepository->findWithStats();
    }

    /**
     * Prepara i dati dell'utente per il salvataggio
     */
    private function prepareUserData(array $data, ?int $userId = null): array
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
     * Valida i dati dell'utente
     */
    public function validateUserData(array $data, ?int $userId = null): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Il nome è obbligatorio';
        } elseif (strlen($data['name']) < 2) {
            $errors[] = 'Il nome deve essere di almeno 2 caratteri';
        } elseif (strlen($data['name']) > 255) {
            $errors[] = 'Il nome non può superare i 255 caratteri';
        }

        if (empty($data['email'])) {
            $errors[] = 'L\'email è obbligatoria';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L\'email non è valida';
        } elseif ($this->emailExists($data['email'], $userId)) {
            $errors[] = 'L\'email è già in uso';
        }

        if (empty($data['password']) && !$userId) {
            $errors[] = 'La password è obbligatoria per i nuovi utenti';
        } elseif (!empty($data['password']) && strlen($data['password']) < 8) {
            $errors[] = 'La password deve essere di almeno 8 caratteri';
        }

        if (isset($data['role']) && !in_array($data['role'], ['user', 'editor', 'admin'])) {
            $errors[] = 'Il ruolo deve essere "user", "editor" o "admin"';
        }

        return $errors;
    }

    /**
     * Verifica se un'email esiste già
     */
    private function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        $query = User::where('email', $email);
        
        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->exists();
    }

    /**
     * Genera statistiche dettagliate per un utente
     */
    public function getUserDetailedStats(int $userId): array
    {
        $user = $this->getUserById($userId);
        if (!$user) {
            return [];
        }

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
