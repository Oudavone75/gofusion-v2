<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $guarded = ['id', '_token'];

    public function getImageAttribute($value)
    {
        return $value ? asset('/' . ltrim($value, '/')) : null;
    }

    public function events()
    {
        return $this->belongsToMany(
            related: Event::class,
            table: 'event_themes',
            foreignPivotKey: 'theme_id',
            relatedPivotKey: 'event_id'
        );
    }
}
