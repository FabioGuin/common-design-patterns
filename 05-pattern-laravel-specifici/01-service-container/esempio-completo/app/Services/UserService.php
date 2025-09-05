<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EmailService $emailService,
        private CacheService $cacheService
    ) {}

    /**
     * Crea un nuovo utente
     */
    public function createUser(array $data): User
    {
        Log::info('UserService: Creating user', ['data' => $data]);

        // Crea l'utente
        $user = $this->userRepository->create($data);

        // Invia email di benvenuto
        $this->emailService->sendWelcomeEmail($user);

        // Pulisce la cache
        $this->cacheService->forget('users.list');

        Log::info('UserService: User created successfully', ['user_id' => $user->id]);

        return $user;
    }

    /**
     * Aggiorna un utente esistente
     */
    public function updateUser(int $id, array $data): User
    {
        Log::info('UserService: Updating user', ['user_id' => $id, 'data' => $data]);

        $user = $this->userRepository->update($id, $data);

        // Pulisce la cache
        $this->cacheService->forget('users.list');
        $this->cacheService->forget("users.{$id}");

        Log::info('UserService: User updated successfully', ['user_id' => $id]);

        return $user;
    }

    /**
     * Recupera un utente per ID
     */
    public function getUserById(int $id): User
    {
        $cacheKey = "users.{$id}";

        return $this->cacheService->remember($cacheKey, 3600, function () use ($id) {
            return $this->userRepository->findById($id);
        });
    }

    /**
     * Recupera tutti gli utenti
     */
    public function getAllUsers(): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = 'users.list';

        return $this->cacheService->remember($cacheKey, 1800, function () {
            return $this->userRepository->findAll();
        });
    }

    /**
     * Elimina un utente
     */
    public function deleteUser(int $id): bool
    {
        Log::info('UserService: Deleting user', ['user_id' => $id]);

        $result = $this->userRepository->delete($id);

        if ($result) {
            // Pulisce la cache
            $this->cacheService->forget('users.list');
            $this->cacheService->forget("users.{$id}");

            Log::info('UserService: User deleted successfully', ['user_id' => $id]);
        }

        return $result;
    }

    /**
     * Attiva un utente
     */
    public function activateUser(int $id): User
    {
        Log::info('UserService: Activating user', ['user_id' => $id]);

        $user = $this->userRepository->activate($id);

        // Invia notifica di attivazione
        $this->emailService->sendActivationEmail($user);

        // Pulisce la cache
        $this->cacheService->forget('users.list');
        $this->cacheService->forget("users.{$id}");

        Log::info('UserService: User activated successfully', ['user_id' => $id]);

        return $user;
    }

    /**
     * Disattiva un utente
     */
    public function deactivateUser(int $id): User
    {
        Log::info('UserService: Deactivating user', ['user_id' => $id]);

        $user = $this->userRepository->deactivate($id);

        // Pulisce la cache
        $this->cacheService->forget('users.list');
        $this->cacheService->forget("users.{$id}");

        Log::info('UserService: User deactivated successfully', ['user_id' => $id]);

        return $user;
    }

    /**
     * Recupera statistiche degli utenti
     */
    public function getUserStats(): array
    {
        $cacheKey = 'users.stats';

        return $this->cacheService->remember($cacheKey, 3600, function () {
            return [
                'total_users' => $this->userRepository->count(),
                'active_users' => $this->userRepository->countActive(),
                'inactive_users' => $this->userRepository->countInactive(),
                'new_users_today' => $this->userRepository->countNewToday(),
                'new_users_this_week' => $this->userRepository->countNewThisWeek(),
                'new_users_this_month' => $this->userRepository->countNewThisMonth(),
            ];
        });
    }

    /**
     * Cerca utenti
     */
    public function searchUsers(string $term): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "users.search.{$term}";

        return $this->cacheService->remember($cacheKey, 900, function () use ($term) {
            return $this->userRepository->search($term);
        });
    }

    /**
     * Recupera utenti per pagina
     */
    public function getUsersPaginated(int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $cacheKey = "users.paginated.{$perPage}." . request()->get('page', 1);

        return $this->cacheService->remember($cacheKey, 600, function () use ($perPage) {
            return $this->userRepository->paginate($perPage);
        });
    }

    /**
     * Verifica se un utente esiste
     */
    public function userExists(int $id): bool
    {
        $cacheKey = "users.exists.{$id}";

        return $this->cacheService->remember($cacheKey, 3600, function () use ($id) {
            return $this->userRepository->exists($id);
        });
    }

    /**
     * Recupera utenti recenti
     */
    public function getRecentUsers(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "users.recent.{$limit}";

        return $this->cacheService->remember($cacheKey, 1800, function () use ($limit) {
            return $this->userRepository->findRecent($limit);
        });
    }

    /**
     * Pulisce la cache degli utenti
     */
    public function clearUserCache(): void
    {
        $this->cacheService->forget('users.*');
        Log::info('UserService: User cache cleared');
    }
}
