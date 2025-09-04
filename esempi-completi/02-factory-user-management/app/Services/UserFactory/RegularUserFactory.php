<?php

namespace App\Services\UserFactory;

use App\Models\User;

class RegularUserFactory extends UserFactory
{
    public function getUserType(): string
    {
        return 'user';
    }
    
    public function getDefaultPermissions(): array
    {
        return [
            'profile.read',
            'profile.update',
            'posts.create',
            'posts.read',
            'posts.update',
            'comments.create',
            'comments.read'
        ];
    }
    
    protected function createSpecificUser(array $data): User
    {
        return new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'] ?? 'user123'),
            'email_verified_at' => null, // Richiede verifica email
            'is_active' => true,
            'preferences' => json_encode($data['preferences'] ?? []),
            'last_login_at' => null
        ]);
    }
}
