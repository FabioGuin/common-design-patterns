<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\UserFactory;
use App\Models\User;

class UserFactoryTest extends TestCase
{
    /** @test */
    public function it_creates_admin_user()
    {
        $user = UserFactory::createUser('admin', 'Admin User', 'admin@example.com');
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('admin', $user->role);
        $this->assertTrue($user->isActive);
        $this->assertEquals('IT', $user->department);
        $this->assertContains('manage_users', $user->permissions);
        $this->assertTrue($user->canAccess('admin-panel'));
    }

    /** @test */
    public function it_creates_regular_user()
    {
        $user = UserFactory::createUser('regular', 'Regular User', 'regular@example.com');
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('regular', $user->role);
        $this->assertTrue($user->isActive);
        $this->assertEquals('General', $user->department);
        $this->assertContains('read', $user->permissions);
        $this->assertFalse($user->canAccess('admin-panel'));
    }

    /** @test */
    public function it_creates_guest_user()
    {
        $user = UserFactory::createUser('guest', 'Guest User', 'guest@example.com');
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('guest', $user->role);
        $this->assertFalse($user->isActive);
        $this->assertNull($user->department);
        $this->assertContains('read_public', $user->permissions);
        $this->assertFalse($user->canAccess('admin-panel'));
    }

    /** @test */
    public function it_throws_exception_for_unsupported_role()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported user role: invalid');
        
        UserFactory::createUser('invalid', 'Test User', 'test@example.com');
    }

    /** @test */
    public function it_returns_supported_roles()
    {
        $roles = UserFactory::getSupportedRoles();
        
        $this->assertIsArray($roles);
        $this->assertContains('admin', $roles);
        $this->assertContains('regular', $roles);
        $this->assertContains('guest', $roles);
    }

    /** @test */
    public function it_allows_registering_new_factory()
    {
        // Crea una factory personalizzata
        $customFactory = new class implements \App\Services\UserFactoryInterface {
            public function createUser(string $name, string $email): User
            {
                return new User($name, $email, 'custom', ['custom_permission'], true, 'Custom Dept');
            }
        };
        
        // Registra la factory
        UserFactory::registerFactory('custom', get_class($customFactory));
        
        // Verifica che sia stata registrata
        $roles = UserFactory::getSupportedRoles();
        $this->assertContains('custom', $roles);
        
        // Testa la creazione
        $user = UserFactory::createUser('custom', 'Custom User', 'custom@example.com');
        $this->assertEquals('custom', $user->role);
        $this->assertEquals('Custom Dept', $user->department);
    }

    /** @test */
    public function it_throws_exception_for_invalid_factory_class()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Factory must implement UserFactoryInterface');
        
        UserFactory::registerFactory('invalid', \stdClass::class);
    }

    /** @test */
    public function it_creates_users_with_different_configurations()
    {
        $admin = UserFactory::createUser('admin', 'Admin', 'admin@test.com');
        $regular = UserFactory::createUser('regular', 'Regular', 'regular@test.com');
        $guest = UserFactory::createUser('guest', 'Guest', 'guest@test.com');
        
        // Verifica che ogni tipo abbia configurazioni diverse
        $this->assertNotEquals($admin->permissions, $regular->permissions);
        $this->assertNotEquals($regular->permissions, $guest->permissions);
        $this->assertNotEquals($admin->department, $regular->department);
        $this->assertNotEquals($admin->isActive, $guest->isActive);
    }
}
