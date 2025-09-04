<?php

namespace App\Services\UserFactory;

use App\Models\User;

class AdminUserFactory extends UserFactory
{
    public function getUserType(): string
    {
        return 'admin';
    }
    
    public function getDefaultPermissions(): array
    {
        return [
            'users.create',
            'users.read',
            'users.update',
            'users.delete',
            'roles.manage',
            'system.settings',
            'logs.view',
            'backup.create'
        ];
    }
    
    protected function createSpecificUser(array $data): User
    {
        return new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'] ?? 'admin123'),
            'email_verified_at' => now(),
            'is_active' => true,
            'admin_notes' => $data['admin_notes'] ?? null,
            'last_login_at' => null
        ]);
    }
}
