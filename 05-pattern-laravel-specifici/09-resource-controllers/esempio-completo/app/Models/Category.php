<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relationships
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getPostsCountAttribute()
    {
        return $this->posts()->count();
    }

    public function getPublishedPostsCountAttribute()
    {
        return $this->posts()->published()->count();
    }

    public function getIsActiveDisplayAttribute()
    {
        return $this->is_active ? 'Attiva' : 'Inattiva';
    }

    public function getColorDisplayAttribute()
    {
        return $this->color ?: '#6B7280';
    }

    public function getIconDisplayAttribute()
    {
        return $this->icon ?: 'folder';
    }

    // Mutators
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = \Str::slug($value);
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = strip_tags($value);
    }

    // Methods
    public function isActive()
    {
        return $this->is_active;
    }

    public function isInactive()
    {
        return !$this->is_active;
    }

    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function hasPosts()
    {
        return $this->posts()->count() > 0;
    }

    public function hasPublishedPosts()
    {
        return $this->posts()->published()->count() > 0;
    }

    public function canBeDeleted()
    {
        return !$this->hasPosts();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getRouteKey()
    {
        return $this->slug;
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?: $this->getRouteKeyName(), $value)->first();
    }
}
