<?php

namespace App\Models;

use App\Events\User\UserLoggedIn;
use App\Events\User\UserRegistered;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'date_of_birth',
        'gender',
        'newsletter',
        'terms_accepted',
        'last_login_at',
        'address'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'newsletter' => 'boolean',
        'terms_accepted' => 'boolean',
        'last_login_at' => 'datetime',
        'address' => 'array'
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (User $user) {
            // Fire user registered event
            event(new UserRegistered($user, [
                'registration_ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'source' => 'web'
            ]));
        });
    }

    /**
     * Handle user login and fire event.
     */
    public function handleLogin(string $ipAddress, string $userAgent): void
    {
        $this->update(['last_login_at' => now()]);

        event(new UserLoggedIn($this, $ipAddress, $userAgent, [
            'login_method' => 'password',
            'session_id' => session()->getId()
        ]));
    }

    /**
     * Get user's full address.
     */
    public function getFullAddress(): ?string
    {
        if (!$this->address) {
            return null;
        }

        return implode(', ', array_filter([
            $this->address['street'] ?? null,
            $this->address['city'] ?? null,
            $this->address['postal_code'] ?? null,
            $this->address['country'] ?? null
        ]));
    }

    /**
     * Check if user has phone number.
     */
    public function hasPhone(): bool
    {
        return !empty($this->phone);
    }

    /**
     * Check if user is verified.
     */
    public function isVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Check if user accepts newsletter.
     */
    public function acceptsNewsletter(): bool
    {
        return $this->newsletter;
    }

    /**
     * Get user's age.
     */
    public function getAge(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }

        return $this->date_of_birth->age;
    }

    /**
     * Check if user is minor.
     */
    public function isMinor(): bool
    {
        $age = $this->getAge();
        return $age !== null && $age < 18;
    }
}
