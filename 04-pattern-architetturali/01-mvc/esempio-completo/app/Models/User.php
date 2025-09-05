<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'bio',
        'avatar',
        'role',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relazione con gli articoli dell'utente
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Scope per utenti attivi
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope per utenti con ruolo specifico
     */
    public function scopeWithRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope per utenti che hanno scritto articoli
     */
    public function scopeWithArticles($query)
    {
        return $query->whereHas('articles');
    }

    /**
     * Accessor per il nome formattato
     */
    public function getFormattedNameAttribute(): string
    {
        return ucwords(strtolower($this->name));
    }

    /**
     * Accessor per l'avatar con fallback
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Fallback a Gravatar o avatar generico
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Accessor per il ruolo formattato
     */
    public function getFormattedRoleAttribute(): string
    {
        return ucfirst($this->role ?? 'user');
    }

    /**
     * Accessor per il numero di articoli pubblicati
     */
    public function getPublishedArticlesCountAttribute(): int
    {
        return $this->articles()->published()->count();
    }

    /**
     * Accessor per il numero totale di articoli
     */
    public function getTotalArticlesCountAttribute(): int
    {
        return $this->articles()->count();
    }

    /**
     * Mutator per il nome (rimuove spazi extra)
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = trim($value);
    }

    /**
     * Mutator per l'email (converte in lowercase)
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower(trim($value));
    }

    /**
     * Metodo per verificare se l'utente è admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Metodo per verificare se l'utente è editor
     */
    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    /**
     * Metodo per verificare se l'utente è attivo
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Metodo per attivare l'utente
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Metodo per disattivare l'utente
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Metodo per ottenere l'URL del profilo utente
     */
    public function getProfileUrlAttribute(): string
    {
        return route('users.show', $this->id);
    }

    /**
     * Metodo per ottenere gli articoli recenti dell'utente
     */
    public function getRecentArticles($limit = 5)
    {
        return $this->articles()
                   ->published()
                   ->recent()
                   ->limit($limit)
                   ->get();
    }

    /**
     * Metodo per ottenere le statistiche dell'utente
     */
    public function getStats(): array
    {
        return [
            'total_articles' => $this->total_articles_count,
            'published_articles' => $this->published_articles_count,
            'draft_articles' => $this->articles()->draft()->count(),
            'member_since' => $this->created_at->diffForHumans(),
        ];
    }
}
