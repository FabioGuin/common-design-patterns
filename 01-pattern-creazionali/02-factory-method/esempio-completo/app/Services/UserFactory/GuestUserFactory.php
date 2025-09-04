<?php

namespace App\Services\UserFactory;

use App\Models\User;

class GuestUserFactory extends UserFactory
{
    public function getUserType(): string
    {
        return 'guest';
    }
    
    public function getDefaultPermissions(): array
    {
        return [
            'posts.read',
            'comments.read',
            'profile.read'
        ];
    }
    
    protected function createSpecificUser(array $data): User
    {
        return new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => null, // Guest non ha password
            'email_verified_at' => null,
            'is_active' => true,
            'guest_session_id' => $data['session_id'] ?? null,
            'last_login_at' => null
        ]);
    }
}
