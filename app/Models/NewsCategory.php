<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    protected $guarded = ['id', '_token'];

    public function news()
    {
        return $this->hasMany(News::class);
    }
}
