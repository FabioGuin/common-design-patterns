<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\UserFactory\AdminUserFactory;
use App\Services\UserFactory\RegularUserFactory;
use App\Services\UserFactory\GuestUserFactory;
use App\Models\User;
use App\Models\Role;

class UserFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Crea le tabelle per i test
        $this->artisan('migrate');
    }

    /** @test */
    public function admin_factory_creates_admin_user()
    {
        $factory = new AdminUserFactory();
        
        $userData = [
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => 'password123'
        ];
        
        $user = $factory->createUser($userData);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('admin', $factory->getUserType());
        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->hasPermission('users.create'));
        $this->assertTrue($user->hasPermission('system.settings'));
    }

    /** @test */
    public function regular_factory_creates_regular_user()
    {
        $factory = new RegularUserFactory();
        
        $userData = [
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'password' => 'password123'
        ];
        
        $user = $factory->createUser($userData);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('user', $factory->getUserType());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->hasPermission('profile.read'));
        $this->assertTrue($user->hasPermission('posts.create'));
        $this->assertFalse($user->hasPermission('system.settings'));
    }

    /** @test */
    public function guest_factory_creates_guest_user()
    {
        $factory = new GuestUserFactory();
        
        $userData = [
            'name' => 'Guest User',
            'email' => 'guest@test.com',
            'session_id' => 'session123'
        ];
        
        $user = $factory->createUser($userData);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('guest', $factory->getUserType());
        $this->assertTrue($user->isGuest());
        $this->assertTrue($user->hasPermission('posts.read'));
        $this->assertFalse($user->hasPermission('posts.create'));
        $this->assertNull($user->password);
    }

    /** @test */
    public function factory_validates_required_data()
    {
        $factory = new RegularUserFactory();
        
        $this->expectException(\InvalidArgumentException::class);
        $factory->createUser(['name' => 'Test']); // Manca email
    }

    /** @test */
    public function factory_validates_email_format()
    {
        $factory = new RegularUserFactory();
        
        $this->expectException(\InvalidArgumentException::class);
        $factory->createUser([
            'name' => 'Test',
            'email' => 'invalid-email'
        ]);
    }

    /** @test */
    public function factory_assigns_correct_role()
    {
        $adminFactory = new AdminUserFactory();
        $userFactory = new RegularUserFactory();
        $guestFactory = new GuestUserFactory();
        
        $adminUser = $adminFactory->createUser([
            'name' => 'Admin',
            'email' => 'admin@test.com'
        ]);
        
        $regularUser = $userFactory->createUser([
            'name' => 'User',
            'email' => 'user@test.com'
        ]);
        
        $guestUser = $guestFactory->createUser([
            'name' => 'Guest',
            'email' => 'guest@test.com'
        ]);
        
        $this->assertEquals('admin', $adminUser->getUserType());
        $this->assertEquals('user', $regularUser->getUserType());
        $this->assertEquals('guest', $guestUser->getUserType());
    }
}
