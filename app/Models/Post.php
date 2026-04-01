<?php

namespace App\Models;

use App\Enums\PostStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'author_id',
        'author_type',
        'content',
        'status',
        'published_at',
        'approved_by',
        'rejection_reason',
        'company_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'status' => PostStatusEnum::class,
    ];

    /**
     * Get the author of the post (User or Admin)
     */
    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the admin who approved the post
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    /**
     * Get all media attached to the post
     */
    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('order');
    }

    /**
     * Get all reactions on the post
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    /**
     * Get all comments on the post
     */
    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class)->whereNull('parent_comment_id');
    }

    /**
     * Get all reports on the post
     */
    public function reports(): HasMany
    {
        return $this->hasMany(PostReport::class);
    }

    /**
     * Get all comments including replies
     */
    public function allComments(): HasMany
    {
        return $this->hasMany(PostComment::class);
    }

    /**
     * Get reactions count grouped by type
     */
    public function reactionsCount()
    {
        return $this->reactions()
            ->selectRaw('reaction_type, count(*) as count')
            ->groupBy('reaction_type');
    }

    /**
     * Scope to get only approved posts
     */
    public function scopeApproved($query)
    {
        return $query->where('status', PostStatusEnum::APPROVED->value);
    }

    /**
     * Scope to get only pending posts
     */
    public function scopePending($query)
    {
        return $query->where('status', PostStatusEnum::PENDING->value);
    }

    /**
     * Scope to get only rejected posts
     */
    public function scopeRejected($query)
    {
        return $query->where('status', PostStatusEnum::REJECTED->value);
    }

    protected function publishedAt(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) {
                    return null;
                }
                $date = Carbon::parse($this->attributes['published_at']);
                return $date->isToday()
                    ? $date->format('h:i A')
                    : $date->format('d M Y');
            }
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
