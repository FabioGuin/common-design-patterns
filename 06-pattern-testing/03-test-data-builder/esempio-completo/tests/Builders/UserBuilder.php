<?php

namespace Tests\Builders;

use App\Models\User;
use Faker\Factory as Faker;

class UserBuilder
{
    private array $attributes = [];
    private Faker $faker;

    public function __construct()
    {
        $this->faker = Faker::create('it_IT');
        $this->reset();
    }

    public static function new(): self
    {
        return new self();
    }

    public function reset(): self
    {
        $this->attributes = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password123'),
            'phone' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->date('Y-m-d', '2000-01-01'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'newsletter' => $this->faker->boolean(30),
            'terms_accepted' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ];
        return $this;
    }

    public function withName(string $name): self
    {
        $this->attributes['name'] = $name;
        return $this;
    }

    public function withEmail(string $email): self
    {
        $this->attributes['email'] = $email;
        return $this;
    }

    public function withPassword(string $password): self
    {
        $this->attributes['password'] = bcrypt($password);
        return $this;
    }

    public function withPhone(string $phone): self
    {
        $this->attributes['phone'] = $phone;
        return $this;
    }

    public function withDateOfBirth(string $dateOfBirth): self
    {
        $this->attributes['date_of_birth'] = $dateOfBirth;
        return $this;
    }

    public function withGender(string $gender): self
    {
        $this->attributes['gender'] = $gender;
        return $this;
    }

    public function withNewsletter(bool $newsletter): self
    {
        $this->attributes['newsletter'] = $newsletter;
        return $this;
    }

    public function withTermsAccepted(bool $accepted): self
    {
        $this->attributes['terms_accepted'] = $accepted;
        return $this;
    }

    public function withEmailVerified(bool $verified = true): self
    {
        $this->attributes['email_verified_at'] = $verified ? now() : null;
        return $this;
    }

    public function withAddress(array $address): self
    {
        $this->attributes['address'] = $address;
        return $this;
    }

    public function withCreatedAt(string $createdAt): self
    {
        $this->attributes['created_at'] = $createdAt;
        return $this;
    }

    public function withUpdatedAt(string $updatedAt): self
    {
        $this->attributes['updated_at'] = $updatedAt;
        return $this;
    }

    // Convenience methods for common scenarios
    public function asAdmin(): self
    {
        return $this->withEmail('admin@example.com')
                    ->withName('Admin User')
                    ->withNewsletter(false);
    }

    public function asCustomer(): self
    {
        return $this->withEmail('customer@example.com')
                    ->withName('Customer User')
                    ->withNewsletter(true);
    }

    public function asUnverified(): self
    {
        return $this->withEmailVerified(false)
                    ->withTermsAccepted(false);
    }

    public function asMinor(): self
    {
        $birthDate = $this->faker->date('Y-m-d', '2010-01-01');
        return $this->withDateOfBirth($birthDate)
                    ->withTermsAccepted(false);
    }

    public function asSenior(): self
    {
        $birthDate = $this->faker->date('Y-m-d', '1950-01-01', '1970-01-01');
        return $this->withDateOfBirth($birthDate)
                    ->withNewsletter(false);
    }

    public function withItalianAddress(): self
    {
        $address = [
            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'IT',
            'region' => $this->faker->state()
        ];
        
        return $this->withAddress($address);
    }

    public function withAmericanAddress(): self
    {
        $address = [
            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'US',
            'state' => $this->faker->stateAbbr()
        ];
        
        return $this->withAddress($address);
    }

    public function build(): User
    {
        $user = new User($this->attributes);
        
        // Reset for next use
        $this->reset();
        
        return $user;
    }

    public function create(): User
    {
        $user = $this->build();
        $user->save();
        return $user;
    }

    public function make(): User
    {
        return $this->build();
    }

    // Static factory methods for common scenarios
    public static function admin(): User
    {
        return self::new()->asAdmin()->create();
    }

    public static function customer(): User
    {
        return self::new()->asCustomer()->create();
    }

    public static function unverified(): User
    {
        return self::new()->asUnverified()->create();
    }

    public static function minor(): User
    {
        return self::new()->asMinor()->create();
    }

    public static function senior(): User
    {
        return self::new()->asSenior()->create();
    }

    // Bulk creation methods
    public static function createMany(int $count): array
    {
        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $users[] = self::new()->create();
        }
        return $users;
    }

    public static function createAdmins(int $count): array
    {
        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $users[] = self::admin();
        }
        return $users;
    }

    public static function createCustomers(int $count): array
    {
        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $users[] = self::customer();
        }
        return $users;
    }
}
