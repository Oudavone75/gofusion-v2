<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = [
        'campaign_season_id',
        'user_id',
        'amount',
        'company_id',
        'company_department_id',
    ];
}
