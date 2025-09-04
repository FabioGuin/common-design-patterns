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
        'email_verified_at',
        'is_active',
        'permissions',
        'admin_notes',
        'preferences',
        'guest_session_id',
        'last_login_at',
        'role_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'permissions' => 'array',
        'preferences' => 'array'
    ];

    /**
     * Relazione con il ruolo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Verifica se l'utente ha un permesso specifico
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Verifica se l'utente è admin
     */
    public function isAdmin(): bool
    {
        return $this->role?->name === 'admin';
    }

    /**
     * Verifica se l'utente è guest
     */
    public function isGuest(): bool
    {
        return $this->role?->name === 'guest';
    }

    /**
     * Restituisce il tipo di utente
     */
    public function getUserType(): string
    {
        return $this->role?->name ?? 'unknown';
    }
}
