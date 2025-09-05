<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'excerpt',
        'slug',
        'category_id',
        'user_id',
        'status',
        'featured_image',
        'meta_description',
        'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%")
              ->orWhere('excerpt', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }

        return $this->content ? substr(strip_tags($this->content), 0, 150) . '...' : '';
    }

    public function getReadingTimeAttribute()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / 200); // 200 words per minute
        return $minutes . ' min';
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'published' => 'Pubblicato',
            'draft' => 'Bozza',
            'archived' => 'Archiviato',
            default => 'Sconosciuto'
        };
    }

    // Mutators
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = \Str::slug($value);
    }

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = $value;
        
        // Auto-generate excerpt if not provided
        if (empty($this->attributes['excerpt'])) {
            $this->attributes['excerpt'] = substr(strip_tags($value), 0, 150) . '...';
        }
    }

    // Methods
    public function isPublished()
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isArchived()
    {
        return $this->status === 'archived';
    }

    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now()
        ]);
    }

    public function archive()
    {
        $this->update(['status' => 'archived']);
    }

    public function restore()
    {
        $this->update(['status' => 'draft']);
    }

    public function getCommentCountAttribute()
    {
        return $this->comments()->count();
    }

    public function getApprovedCommentCountAttribute()
    {
        return $this->comments()->where('approved', true)->count();
    }
}
