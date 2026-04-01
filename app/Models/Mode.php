<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mode extends Model
{
    protected $guarded = ['id', '_token'];

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

}
