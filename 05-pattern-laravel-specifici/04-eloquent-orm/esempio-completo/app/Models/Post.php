<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'status',
        'published_at',
        'user_id',
        'category_id',
        'meta_title',
        'meta_description',
        'views_count',
        'likes_count',
        'comments_count',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'published_at' => 'datetime',
        'views_count' => 'integer',
        'likes_count' => 'integer',
        'comments_count' => 'integer',
    ];

    /**
     * Get the user that owns the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that the post belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the comments for the post.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the tags for the post.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Get the users who liked the post.
     */
    public function likedBy()
    {
        return $this->belongsToMany(User::class, 'post_likes')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include posts by a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include posts in a specific category.
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to search posts by title or content.
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
     * Scope a query to only include posts with specific tags.
     */
    public function scopeWithTags($query, $tags)
    {
        return $query->whereHas('tags', function ($q) use ($tags) {
            $q->whereIn('name', $tags);
        });
    }

    /**
     * Scope a query to order posts by popularity.
     */
    public function scopePopular($query)
    {
        return $query->orderBy('views_count', 'desc')
                    ->orderBy('likes_count', 'desc')
                    ->orderBy('comments_count', 'desc');
    }

    /**
     * Scope a query to order posts by recent activity.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    /**
     * Get the post's excerpt.
     */
    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        return Str::limit(strip_tags($this->content), 150);
    }

    /**
     * Get the post's reading time.
     */
    public function getReadingTimeAttribute()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / 200); // 200 words per minute
        
        return $minutes . ' min read';
    }

    /**
     * Get the post's featured image URL.
     */
    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return asset('storage/posts/' . $this->featured_image);
        }
        
        return 'https://via.placeholder.com/800x400?text=' . urlencode($this->title);
    }

    /**
     * Get the post's status badge.
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'published' => 'success',
            'draft' => 'warning',
            'archived' => 'secondary',
        ];
        
        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Get the post's status text.
     */
    public function getStatusTextAttribute()
    {
        $texts = [
            'published' => 'Pubblicato',
            'draft' => 'Bozza',
            'archived' => 'Archiviato',
        ];
        
        return $texts[$this->status] ?? 'Sconosciuto';
    }

    /**
     * Get the post's formatted published date.
     */
    public function getFormattedPublishedDateAttribute()
    {
        if (!$this->published_at) {
            return 'Non pubblicato';
        }
        
        return $this->published_at->format('d/m/Y H:i');
    }

    /**
     * Get the post's relative published date.
     */
    public function getRelativePublishedDateAttribute()
    {
        if (!$this->published_at) {
            return 'Non pubblicato';
        }
        
        return $this->published_at->diffForHumans();
    }

    /**
     * Check if the post is published.
     */
    public function isPublished()
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    /**
     * Check if the post is draft.
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the post is archived.
     */
    public function isArchived()
    {
        return $this->status === 'archived';
    }

    /**
     * Publish the post.
     */
    public function publish()
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Unpublish the post.
     */
    public function unpublish()
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Archive the post.
     */
    public function archive()
    {
        $this->update(['status' => 'archived']);
    }

    /**
     * Increment the post's views count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Increment the post's likes count.
     */
    public function incrementLikes()
    {
        $this->increment('likes_count');
    }

    /**
     * Decrement the post's likes count.
     */
    public function decrementLikes()
    {
        $this->decrement('likes_count');
    }

    /**
     * Update the post's comments count.
     */
    public function updateCommentsCount()
    {
        $this->update(['comments_count' => $this->comments()->count()]);
    }

    /**
     * Get the post's related posts.
     */
    public function getRelatedPosts($limit = 5)
    {
        return static::where('id', '!=', $this->id)
                    ->where('status', 'published')
                    ->where(function ($query) {
                        $query->where('category_id', $this->category_id)
                              ->orWhereHas('tags', function ($q) {
                                  $q->whereIn('id', $this->tags->pluck('id'));
                              });
                    })
                    ->limit($limit)
                    ->get();
    }

    /**
     * Get the post's statistics.
     */
    public function getStats()
    {
        return [
            'views_count' => $this->views_count,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'reading_time' => $this->reading_time,
            'word_count' => str_word_count(strip_tags($this->content)),
            'character_count' => strlen(strip_tags($this->content)),
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title')) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::deleting(function ($post) {
            // Soft delete related comments
            $post->comments()->delete();
        });
    }
}
