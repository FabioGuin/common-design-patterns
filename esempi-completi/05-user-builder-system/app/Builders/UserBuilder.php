<?php

namespace App\Builders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserBuilder
{
    private array $userData = [];
    private array $profileData = [];
    private array $settingsData = [];
    private array $roles = [];

    public static function create(): self
    {
        return new self();
    }

    public function withBasicInfo(string $firstName, string $lastName, string $email): self
    {
        $this->userData = array_merge($this->userData, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ]);

        return $this;
    }

    public function withPassword(string $password): self
    {
        $this->userData['password'] = Hash::make($password);
        return $this;
    }

    public function withProfile(array $profileData): self
    {
        $this->profileData = array_merge($this->profileData, $profileData);
        return $this;
    }

    public function withSettings(array $settingsData): self
    {
        $this->settingsData = array_merge($this->settingsData, $settingsData);
        return $this;
    }

    public function withRole(string $role): self
    {
        $this->roles[] = $role;
        return $this;
    }

    public function withRoles(array $roles): self
    {
        $this->roles = array_merge($this->roles, $roles);
        return $this;
    }

    public function asAdmin(): self
    {
        $this->roles[] = 'admin';
        return $this;
    }

    public function asEditor(): self
    {
        $this->roles[] = 'editor';
        return $this;
    }

    public function asUser(): self
    {
        $this->roles[] = 'user';
        return $this;
    }

    public function withEmailVerified(): self
    {
        $this->userData['email_verified_at'] = now();
        return $this;
    }

    public function withPhone(string $phone): self
    {
        $this->userData['phone'] = $phone;
        return $this;
    }

    public function withAddress(string $address, string $city, string $postalCode, string $country): self
    {
        $this->userData = array_merge($this->userData, [
            'address' => $address,
            'city' => $city,
            'postal_code' => $postalCode,
            'country' => $country,
        ]);

        return $this;
    }

    public function withBirthDate(string $birthDate): self
    {
        $this->userData['birth_date'] = $birthDate;
        return $this;
    }

    public function isActive(bool $active = true): self
    {
        $this->userData['is_active'] = $active;
        return $this;
    }

    public function withCustomData(array $data): self
    {
        $this->userData = array_merge($this->userData, $data);
        return $this;
    }

    public function build(): User
    {
        $this->validateData();

        $user = User::create($this->userData);

        if (!empty($this->profileData)) {
            $user->profile()->create($this->profileData);
        }

        if (!empty($this->settingsData)) {
            $user->settings()->create($this->settingsData);
        }

        if (!empty($this->roles)) {
            $user->roles()->sync($this->roles);
        }

        return $user;
    }

    private function validateData(): void
    {
        $userRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ];

        $profileRules = [
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
        ];

        $settingsRules = [
            'notifications' => 'nullable|boolean',
            'theme' => 'nullable|string|in:light,dark,auto',
            'language' => 'nullable|string|in:it,en,es,fr',
            'timezone' => 'nullable|string|max:50',
        ];

        $userValidator = Validator::make($this->userData, $userRules);
        if ($userValidator->fails()) {
            throw new ValidationException($userValidator);
        }

        if (!empty($this->profileData)) {
            $profileValidator = Validator::make($this->profileData, $profileRules);
            if ($profileValidator->fails()) {
                throw new ValidationException($profileValidator);
            }
        }

        if (!empty($this->settingsData)) {
            $settingsValidator = Validator::make($this->settingsData, $settingsRules);
            if ($settingsValidator->fails()) {
                throw new ValidationException($settingsValidator);
            }
        }
    }

    public function getData(): array
    {
        return [
            'user' => $this->userData,
            'profile' => $this->profileData,
            'settings' => $this->settingsData,
            'roles' => $this->roles,
        ];
    }
}
