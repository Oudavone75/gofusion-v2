<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoSessionStep extends Model
{
    protected $fillable = [
        'go_session_id',
        'created_by',
        'title',
        'description',
        'status',
        'position'
    ];

    public function campaignSeason()
    {
        return $this->belongsTo(CampaignsSeason::class, 'campaign_season_id');
    }

    public function goSession()
    {
        return $this->belongsTo(GoSession::class);
    }

    public function quizStep()
    {
        return $this->hasOne(Quiz::class, 'go_session_step_id');
    }

    public function imageSubmissionGuideline()
    {
        return $this->hasOne(ImageSubmissionGuideline::class, 'go_session_step_id');
    }

    public function spinWheelStep()
    {
        return $this->hasOne(SpinWheel::class, 'go_session_step_id');
    }

    public function surveyStep()
    {
        return $this->hasOne(SurvayFeedback::class, 'go_session_step_id');
    }

}
