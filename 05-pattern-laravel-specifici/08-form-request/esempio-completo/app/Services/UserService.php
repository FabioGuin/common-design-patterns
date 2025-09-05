<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function createUser(array $userData): User
    {
        // Hash della password
        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        // Imposta valori di default
        $userData = array_merge([
            'role' => 'user',
            'email_verified_at' => now()
        ], $userData);

        $user = User::create($userData);

        Log::info('Utente creato', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role
        ]);

        return $user;
    }

    public function updateUser(User $user, array $userData): User
    {
        // Hash della password se fornita
        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        // Rimuovi password attuale se non fornita
        if (isset($userData['current_password'])) {
            unset($userData['current_password']);
        }

        $user->update($userData);

        Log::info('Utente aggiornato', [
            'user_id' => $user->id,
            'updated_fields' => array_keys($userData)
        ]);

        return $user;
    }

    public function deleteUser(User $user): bool
    {
        $userId = $user->id;
        $userEmail = $user->email;

        $deleted = $user->delete();

        if ($deleted) {
            Log::info('Utente eliminato', [
                'user_id' => $userId,
                'email' => $userEmail
            ]);
        }

        return $deleted;
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Verifica password attuale
        if (!Hash::check($currentPassword, $user->password)) {
            return false;
        }

        // Aggiorna password
        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        Log::info('Password cambiata', [
            'user_id' => $user->id
        ]);

        return true;
    }

    public function updateRole(User $user, string $role): User
    {
        $oldRole = $user->role;
        
        $user->update(['role' => $role]);

        Log::info('Ruolo utente aggiornato', [
            'user_id' => $user->id,
            'old_role' => $oldRole,
            'new_role' => $role
        ]);

        return $user;
    }

    public function getUserStats(): array
    {
        $totalUsers = User::count();
        $adminUsers = User::where('role', 'admin')->count();
        $moderatorUsers = User::where('role', 'moderator')->count();
        $regularUsers = User::where('role', 'user')->count();

        return [
            'total' => $totalUsers,
            'admins' => $adminUsers,
            'moderators' => $moderatorUsers,
            'users' => $regularUsers
        ];
    }
}
