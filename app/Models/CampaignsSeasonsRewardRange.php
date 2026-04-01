<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignsSeasonsRewardRange extends Model
{

    

    protected $fillable = [
        'campaign_season_id',
        'rank_start',
        'rank_end',
        'reward',
    ];
}
