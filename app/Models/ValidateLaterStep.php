<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidateLaterStep extends Model
{
    protected $guarded = ['id', '_token'];
}
