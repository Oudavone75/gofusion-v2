<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentLike extends Model
{
    protected $fillable = [
        'comment_id',
        'user_id',
    ];

    /**
     * Get the comment that was liked
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(PostComment::class, 'comment_id');
    }

    /**
     * Get the user who liked the comment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
