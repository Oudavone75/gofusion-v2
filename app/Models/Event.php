<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class Event extends Model
{
    protected $guarded = ['id', '_token'];

    public function eventable(): MorphTo
    {
        return $this->morphTo();
    }

    public function eventGuideline()
    {
        return $this->hasMany(EventSubmissionGuideline::class, 'event_id');
    }

    public function eventStep()
    {
        return $this->hasMany(EventSubmissionStep::class, 'event_id');
    }

    public function eventCategories()
    {
        return $this->belongsToMany(Category::class, 'event_categories', 'event_id', 'category_id');
    }

    public function themes()
    {
        return $this->belongsToMany(
            related: Theme::class,
            table: 'event_themes',
            foreignPivotKey: 'event_id',
            relatedPivotKey: 'theme_id'
        );
    }
}
