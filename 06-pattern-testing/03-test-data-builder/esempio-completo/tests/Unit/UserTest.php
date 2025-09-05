<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\Builders\UserBuilder;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_user_with_default_values()
    {
        $user = UserBuilder::new()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->name);
        $this->assertNotEmpty($user->email);
        $this->assertTrue($user->terms_accepted);
        $this->assertNotNull($user->email_verified_at);
    }

    /** @test */
    public function it_creates_user_with_custom_values()
    {
        $user = UserBuilder::new()
            ->withName('John Doe')
            ->withEmail('john@example.com')
            ->withPassword('custompassword')
            ->withPhone('+393401234567')
            ->withNewsletter(false)
            ->create();

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('+393401234567', $user->phone);
        $this->assertFalse($user->newsletter);
        $this->assertTrue(password_verify('custompassword', $user->password));
    }

    /** @test */
    public function it_creates_admin_user()
    {
        $admin = UserBuilder::admin();

        $this->assertEquals('admin@example.com', $admin->email);
        $this->assertEquals('Admin User', $admin->name);
        $this->assertFalse($admin->newsletter);
    }

    /** @test */
    public function it_creates_customer_user()
    {
        $customer = UserBuilder::customer();

        $this->assertEquals('customer@example.com', $customer->email);
        $this->assertEquals('Customer User', $customer->name);
        $this->assertTrue($customer->newsletter);
    }

    /** @test */
    public function it_creates_unverified_user()
    {
        $user = UserBuilder::unverified();

        $this->assertNull($user->email_verified_at);
        $this->assertFalse($user->terms_accepted);
    }

    /** @test */
    public function it_creates_minor_user()
    {
        $minor = UserBuilder::minor();

        $this->assertFalse($minor->terms_accepted);
        $this->assertTrue($minor->date_of_birth > '2010-01-01');
    }

    /** @test */
    public function it_creates_senior_user()
    {
        $senior = UserBuilder::senior();

        $this->assertFalse($senior->newsletter);
        $this->assertTrue($senior->date_of_birth < '1970-01-01');
    }

    /** @test */
    public function it_creates_user_with_italian_address()
    {
        $user = UserBuilder::withItalianAddress();

        $this->assertIsArray($user->address);
        $this->assertEquals('IT', $user->address['country']);
        $this->assertArrayHasKey('region', $user->address);
    }

    /** @test */
    public function it_creates_user_with_american_address()
    {
        $user = UserBuilder::withAmericanAddress();

        $this->assertIsArray($user->address);
        $this->assertEquals('US', $user->address['country']);
        $this->assertArrayHasKey('state', $user->address);
    }

    /** @test */
    public function it_creates_multiple_users()
    {
        $users = UserBuilder::createMany(5);

        $this->assertCount(5, $users);
        $this->assertContainsOnlyInstancesOf(User::class, $users);
    }

    /** @test */
    public function it_creates_multiple_admins()
    {
        $admins = UserBuilder::createAdmins(3);

        $this->assertCount(3, $admins);
        foreach ($admins as $admin) {
            $this->assertEquals('admin@example.com', $admin->email);
        }
    }

    /** @test */
    public function it_creates_multiple_customers()
    {
        $customers = UserBuilder::createCustomers(4);

        $this->assertCount(4, $customers);
        foreach ($customers as $customer) {
            $this->assertEquals('customer@example.com', $customer->email);
        }
    }

    /** @test */
    public function it_creates_user_with_custom_address()
    {
        $address = [
            'street' => 'Via Roma 123',
            'city' => 'Milano',
            'postal_code' => '20100',
            'country' => 'IT',
            'region' => 'Lombardia'
        ];

        $user = UserBuilder::new()
            ->withAddress($address)
            ->create();

        $this->assertEquals($address, $user->address);
    }

    /** @test */
    public function it_creates_user_with_specific_dates()
    {
        $createdAt = '2023-01-01 10:00:00';
        $updatedAt = '2023-01-02 15:30:00';

        $user = UserBuilder::new()
            ->withCreatedAt($createdAt)
            ->withUpdatedAt($updatedAt)
            ->create();

        $this->assertEquals($createdAt, $user->created_at->format('Y-m-d H:i:s'));
        $this->assertEquals($updatedAt, $user->updated_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_creates_user_with_specific_gender()
    {
        $user = UserBuilder::new()
            ->withGender('other')
            ->create();

        $this->assertEquals('other', $user->gender);
    }

    /** @test */
    public function it_creates_user_with_specific_birth_date()
    {
        $birthDate = '1990-05-15';

        $user = UserBuilder::new()
            ->withDateOfBirth($birthDate)
            ->create();

        $this->assertEquals($birthDate, $user->date_of_birth);
    }

    /** @test */
    public function it_resets_builder_after_build()
    {
        $builder = UserBuilder::new();
        
        // First build
        $user1 = $builder->withName('First User')->build();
        
        // Second build - should not have the name from first build
        $user2 = $builder->build();
        
        $this->assertEquals('First User', $user1->name);
        $this->assertNotEquals('First User', $user2->name);
    }

    /** @test */
    public function it_creates_user_with_fluent_interface()
    {
        $user = UserBuilder::new()
            ->withName('Fluent User')
            ->withEmail('fluent@example.com')
            ->withPassword('fluentpass')
            ->withPhone('+393401234567')
            ->withNewsletter(true)
            ->withTermsAccepted(true)
            ->withEmailVerified(true)
            ->withGender('male')
            ->withDateOfBirth('1985-03-20')
            ->create();

        $this->assertEquals('Fluent User', $user->name);
        $this->assertEquals('fluent@example.com', $user->email);
        $this->assertEquals('+393401234567', $user->phone);
        $this->assertTrue($user->newsletter);
        $this->assertTrue($user->terms_accepted);
        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals('male', $user->gender);
        $this->assertEquals('1985-03-20', $user->date_of_birth);
    }
}
