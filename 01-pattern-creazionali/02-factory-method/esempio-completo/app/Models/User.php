<?php

namespace App\Models;

class User
{
    public string $name;
    public string $email;
    public string $role;
    public array $permissions;
    public bool $isActive;
    public ?string $department;

    public function __construct(
        string $name,
        string $email,
        string $role,
        array $permissions = [],
        bool $isActive = true,
        ?string $department = null
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
        $this->permissions = $permissions;
        $this->isActive = $isActive;
        $this->department = $department;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'permissions' => $this->permissions,
            'is_active' => $this->isActive,
            'department' => $this->department,
            'created_at' => now()->toDateTimeString()
        ];
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    public function canAccess(string $resource): bool
    {
        return match($this->role) {
            'admin' => true,
            'regular' => !in_array($resource, ['admin-panel', 'user-management']),
            'guest' => in_array($resource, ['public-content', 'login']),
            default => false
        };
    }
}
