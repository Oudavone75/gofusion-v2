<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'post_id',
        'user_id',
        'comment',
        'parent_comment_id',
    ];

    /**
     * Get the post that owns the comment
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who commented
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for replies)
     */
    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(PostComment::class, 'parent_comment_id');
    }

    /**
     * Get all replies to this comment
     */
    public function replies(): HasMany
    {
        return $this->hasMany(PostComment::class, 'parent_comment_id');
    }

    /**
     * Get all likes on this comment
     */
    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class, 'comment_id');
    }

    /**
     * Scope to get only parent comments (top-level)
     */
    public function scopeParentComments($query)
    {
        return $query->whereNull('parent_comment_id');
    }

    /**
     * Scope to get only reply comments (2nd level)
     */
    public function scopeReplyComments($query)
    {
        return $query->whereNotNull('parent_comment_id');
    }
}
