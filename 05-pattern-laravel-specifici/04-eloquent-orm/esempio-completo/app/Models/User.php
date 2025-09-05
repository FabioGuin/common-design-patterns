<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'website',
        'location',
        'birth_date',
        'is_active',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user's posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the user's comments.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get the user's liked posts.
     */
    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_likes')
            ->withTimestamps();
    }

    /**
     * Get the user's followed users.
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Get the user's followers.
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include verified users.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope a query to search users by name or email.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
        });
    }

    /**
     * Get the user's full name with title.
     */
    public function getFullNameAttribute()
    {
        return $this->name . ' (' . $this->email . ')';
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }
        
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the user's initials.
     */
    public function getInitialsAttribute()
    {
        $words = explode(' ', $this->name);
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        return $initials;
    }

    /**
     * Get the user's age.
     */
    public function getAgeAttribute()
    {
        if (!$this->birth_date) {
            return null;
        }
        
        return $this->birth_date->age;
    }

    /**
     * Get the user's post count.
     */
    public function getPostCountAttribute()
    {
        return $this->posts()->count();
    }

    /**
     * Get the user's comment count.
     */
    public function getCommentCountAttribute()
    {
        return $this->comments()->count();
    }

    /**
     * Get the user's follower count.
     */
    public function getFollowerCountAttribute()
    {
        return $this->followers()->count();
    }

    /**
     * Get the user's following count.
     */
    public function getFollowingCountAttribute()
    {
        return $this->following()->count();
    }

    /**
     * Check if user is following another user.
     */
    public function isFollowing(User $user)
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    /**
     * Check if user is followed by another user.
     */
    public function isFollowedBy(User $user)
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    /**
     * Follow another user.
     */
    public function follow(User $user)
    {
        if (!$this->isFollowing($user)) {
            $this->following()->attach($user->id);
        }
    }

    /**
     * Unfollow another user.
     */
    public function unfollow(User $user)
    {
        $this->following()->detach($user->id);
    }

    /**
     * Toggle follow status for another user.
     */
    public function toggleFollow(User $user)
    {
        if ($this->isFollowing($user)) {
            $this->unfollow($user);
        } else {
            $this->follow($user);
        }
    }

    /**
     * Get the user's recent activity.
     */
    public function getRecentActivity($limit = 10)
    {
        $posts = $this->posts()->latest()->limit($limit)->get();
        $comments = $this->comments()->latest()->limit($limit)->get();
        
        return $posts->merge($comments)->sortByDesc('created_at')->take($limit);
    }

    /**
     * Get the user's statistics.
     */
    public function getStats()
    {
        return [
            'posts_count' => $this->post_count,
            'comments_count' => $this->comment_count,
            'followers_count' => $this->follower_count,
            'following_count' => $this->following_count,
            'likes_received' => $this->posts()->sum('likes_count'),
            'comments_received' => $this->posts()->sum('comments_count'),
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->slug)) {
                $user->slug = Str::slug($user->name);
            }
        });

        static::updating(function ($user) {
            if ($user->isDirty('name')) {
                $user->slug = Str::slug($user->name);
            }
        });
    }
}
