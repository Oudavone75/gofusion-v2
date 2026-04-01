<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMode extends Model
{
    protected $guarded = ['id', '_token'];
}
