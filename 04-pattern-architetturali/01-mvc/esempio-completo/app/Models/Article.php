<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'excerpt',
        'user_id',
        'published_at',
        'status'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relazione con l'utente che ha scritto l'articolo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope per articoli pubblicati
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at');
    }

    /**
     * Scope per articoli in bozza
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope per articoli recenti
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Scope per ricerca nel titolo e contenuto
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('content', 'like', "%{$term}%")
              ->orWhere('excerpt', 'like', "%{$term}%");
        });
    }

    /**
     * Accessor per il titolo formattato
     */
    public function getFormattedTitleAttribute(): string
    {
        return ucwords(strtolower($this->title));
    }

    /**
     * Accessor per l'excerpt automatico se non fornito
     */
    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return substr(strip_tags($this->content), 0, 150) . '...';
    }

    /**
     * Accessor per il tempo di lettura stimato
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, round($wordCount / 200)); // 200 parole al minuto
    }

    /**
     * Mutator per il titolo (rimuove spazi extra)
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = trim($value);
    }

    /**
     * Mutator per il contenuto (rimuove tag HTML pericolosi)
     */
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = strip_tags($value, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6>');
    }

    /**
     * Metodo per pubblicare l'articolo
     */
    public function publish(): bool
    {
        $this->status = 'published';
        $this->published_at = now();
        return $this->save();
    }

    /**
     * Metodo per mettere in bozza l'articolo
     */
    public function draft(): bool
    {
        $this->status = 'draft';
        $this->published_at = null;
        return $this->save();
    }

    /**
     * Metodo per verificare se l'articolo è pubblicato
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at !== null;
    }

    /**
     * Metodo per verificare se l'articolo è in bozza
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Metodo per ottenere l'URL dell'articolo
     */
    public function getUrlAttribute(): string
    {
        return route('articles.show', $this->id);
    }

    /**
     * Metodo per ottenere il permalink dell'articolo
     */
    public function getPermalinkAttribute(): string
    {
        return route('articles.show', $this->id);
    }
}
