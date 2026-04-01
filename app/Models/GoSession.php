<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoSession extends Model
{
    protected $guarded = ['id', '_token'];

    public function goSessionSteps()
    {
        return $this->hasMany(GoSessionStep::class, 'go_session_id');
    }

    public function campaignSeason()
    {
        return $this->belongsTo(CampaignsSeason::class, 'campaign_season_id');
    }
}
