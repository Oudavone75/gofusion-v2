<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserScore extends Model
{
    protected $fillable = [
        'company_id',
        'company_department_id',
        'user_id',
        'leaves',
        'points',
        'campaign_season_id'
    ];
}
