<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'content',
        'post_id',
        'user_id',
        'parent_id',
        'approved',
        'approved_at',
        'approved_by'
    ];

    protected $casts = [
        'approved' => 'boolean',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relationships
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('approved', false);
    }

    public function scopeForPost($query, $postId)
    {
        return $query->where('post_id', $postId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeReplies($query)
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    // Accessors
    public function getIsReplyAttribute()
    {
        return !is_null($this->parent_id);
    }

    public function getIsApprovedAttribute()
    {
        return $this->approved;
    }

    public function getIsPendingAttribute()
    {
        return !$this->approved;
    }

    public function getStatusDisplayAttribute()
    {
        return $this->approved ? 'Approvato' : 'In attesa';
    }

    public function getContentExcerptAttribute()
    {
        return strlen($this->content) > 100 
            ? substr($this->content, 0, 100) . '...' 
            : $this->content;
    }

    // Mutators
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = strip_tags($value);
    }

    // Methods
    public function approve($userId = null)
    {
        $this->update([
            'approved' => true,
            'approved_at' => now(),
            'approved_by' => $userId ?? auth()->id()
        ]);
    }

    public function reject()
    {
        $this->update([
            'approved' => false,
            'approved_at' => null,
            'approved_by' => null
        ]);
    }

    public function isReply()
    {
        return !is_null($this->parent_id);
    }

    public function isTopLevel()
    {
        return is_null($this->parent_id);
    }

    public function getRepliesCountAttribute()
    {
        return $this->replies()->count();
    }

    public function getApprovedRepliesCountAttribute()
    {
        return $this->replies()->approved()->count();
    }

    public function canBeApproved()
    {
        return !$this->approved;
    }

    public function canBeRejected()
    {
        return $this->approved;
    }

    public function canBeDeleted()
    {
        return $this->replies()->count() === 0;
    }
}
