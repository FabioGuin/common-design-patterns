<?php

namespace App\Models;

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
        'address'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'address' => 'array'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function hasPhone(): bool
    {
        return !empty($this->phone);
    }

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
}
