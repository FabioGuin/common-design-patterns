<?php

namespace App\Services;

use App\Models\User;

interface UserFactoryInterface
{
    public function createUser(string $name, string $email): User;
}

class AdminUserFactory implements UserFactoryInterface
{
    public function createUser(string $name, string $email): User
    {
        return new User(
            name: $name,
            email: $email,
            role: 'admin',
            permissions: [
                'create', 'read', 'update', 'delete',
                'manage_users', 'manage_system', 'access_admin_panel'
            ],
            isActive: true,
            department: 'IT'
        );
    }
}

class RegularUserFactory implements UserFactoryInterface
{
    public function createUser(string $name, string $email): User
    {
        return new User(
            name: $name,
            email: $email,
            role: 'regular',
            permissions: ['read', 'update_own_profile'],
            isActive: true,
            department: 'General'
        );
    }
}

class GuestUserFactory implements UserFactoryInterface
{
    public function createUser(string $name, string $email): User
    {
        return new User(
            name: $name,
            email: $email,
            role: 'guest',
            permissions: ['read_public'],
            isActive: false,
            department: null
        );
    }
}

class UserFactory
{
    private static array $factories = [
        'admin' => AdminUserFactory::class,
        'regular' => RegularUserFactory::class,
        'guest' => GuestUserFactory::class,
    ];

    public static function createUser(string $role, string $name, string $email): User
    {
        if (!isset(self::$factories[$role])) {
            throw new \InvalidArgumentException("Unsupported user role: {$role}");
        }

        $factoryClass = self::$factories[$role];
        $factory = new $factoryClass();
        
        return $factory->createUser($name, $email);
    }

    public static function getSupportedRoles(): array
    {
        return array_keys(self::$factories);
    }

    public static function registerFactory(string $role, string $factoryClass): void
    {
        if (!is_subclass_of($factoryClass, UserFactoryInterface::class)) {
            throw new \InvalidArgumentException("Factory must implement UserFactoryInterface");
        }

        self::$factories[$role] = $factoryClass;
    }
}
