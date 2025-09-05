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
        'role',
        'phone',
        'date_of_birth',
        'is_public',
        'is_banned',
        'banned_at',
        'banned_until'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_public' => 'boolean',
        'is_banned' => 'boolean',
        'banned_at' => 'datetime',
        'banned_until' => 'datetime',
        'password' => 'hashed'
    ];

    // Role methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isBanned(): bool
    {
        return $this->is_banned || 
               ($this->banned_until && $this->banned_until->isFuture());
    }

    public function isPublic(): bool
    {
        return $this->is_public;
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }

    public function canModerate(): bool
    {
        return $this->isAdmin() || $this->isModerator();
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: 'Utente #' . $this->id;
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        return $initials ?: 'U';
    }

    public function getRoleDisplayNameAttribute(): string
    {
        return match($this->role) {
            'admin' => 'Amministratore',
            'moderator' => 'Moderatore',
            'user' => 'Utente',
            default => 'Sconosciuto'
        };
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }

        return $this->date_of_birth->age;
    }

    public function getStatusAttribute(): string
    {
        if ($this->isBanned()) {
            return 'Bannato';
        }

        return 'Attivo';
    }

    // Methods
    public function ban($until = null): void
    {
        $this->update([
            'is_banned' => true,
            'banned_at' => now(),
            'banned_until' => $until
        ]);
    }

    public function unban(): void
    {
        $this->update([
            'is_banned' => false,
            'banned_at' => null,
            'banned_until' => null
        ]);
    }

    public function makePublic(): void
    {
        $this->update(['is_public' => true]);
    }

    public function makePrivate(): void
    {
        $this->update(['is_public' => false]);
    }

    public function changeRole(string $role): void
    {
        $this->update(['role' => $role]);
    }

    // Relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(Comment::class)->where('approved', true);
    }

    public function pendingComments()
    {
        return $this->hasMany(Comment::class)->where('approved', false);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_banned', false)
                    ->where(function ($q) {
                        $q->whereNull('banned_until')
                          ->orWhere('banned_until', '<', now());
                    });
    }

    public function scopeBanned($query)
    {
        return $query->where('is_banned', true)
                    ->orWhere('banned_until', '>', now());
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeModerators($query)
    {
        return $query->where('role', 'moderator');
    }

    public function scopeUsers($query)
    {
        return $query->where('role', 'user');
    }
}
