<?php

namespace App\Services;

use App\Cache\CacheManager;
use App\Models\User;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        private CacheManager $cacheManager
    ) {}

    public function getUser(int $userId): ?array
    {
        return $this->cacheManager->get(
            (string) $userId,
            'users',
            function () use ($userId) {
                return $this->loadUserFromDatabase($userId);
            }
        );
    }

    public function getAllUsers(): array
    {
        return $this->cacheManager->get(
            'all_users',
            'users',
            function () {
                return $this->loadAllUsersFromDatabase();
            }
        );
    }

    public function getUsersByStatus(string $status): array
    {
        return $this->cacheManager->get(
            "users_status_{$status}",
            'users',
            function () use ($status) {
                return $this->loadUsersByStatusFromDatabase($status);
            }
        );
    }

    public function createUser(array $userData): array
    {
        $user = User::create($userData);
        
        // Invalida cache correlata
        $this->invalidateRelatedCache();
        
        return $user->toArray();
    }

    public function updateUser(int $userId, array $userData): array
    {
        $user = User::findOrFail($userId);
        $user->update($userData);
        
        // Invalida cache specifica
        $this->cacheManager->forget((string) $userId, 'users');
        $this->invalidateRelatedCache();
        
        return $user->toArray();
    }

    public function deleteUser(int $userId): bool
    {
        $user = User::findOrFail($userId);
        $result = $user->delete();
        
        // Invalida cache
        $this->cacheManager->forget((string) $userId, 'users');
        $this->invalidateRelatedCache();
        
        return $result;
    }

    public function refreshUser(int $userId): ?array
    {
        return $this->cacheManager->refresh(
            (string) $userId,
            'users',
            function () use ($userId) {
                return $this->loadUserFromDatabase($userId);
            }
        );
    }

    public function preloadUsers(): array
    {
        return $this->cacheManager->preload('users', function () {
            return $this->loadAllUsersFromDatabase();
        });
    }

    private function loadUserFromDatabase(int $userId): ?array
    {
        $user = User::find($userId);
        return $user ? $user->toArray() : null;
    }

    private function loadAllUsersFromDatabase(): array
    {
        return User::all()->toArray();
    }

    private function loadUsersByStatusFromDatabase(string $status): array
    {
        return User::where('status', $status)->get()->toArray();
    }

    private function invalidateRelatedCache(): void
    {
        // Invalida cache per tutti gli status
        $statuses = User::distinct()->pluck('status');
        foreach ($statuses as $status) {
            $this->cacheManager->forget("users_status_{$status}", 'users');
        }
        
        // Invalida cache per tutti gli utenti
        $this->cacheManager->forget('all_users', 'users');
    }

    public function getCacheStats(): array
    {
        return $this->cacheManager->getCacheStats('users');
    }
}
