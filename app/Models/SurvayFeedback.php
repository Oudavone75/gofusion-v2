<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurvayFeedback extends Model
{
    protected $guarded = ['id', '_token'];

    protected $table = 'survey_feedback';

    public function questions()
    {
        return $this->hasMany(SurvayFeedbackQuestion::class, 'survay_feedback_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function goSessionSteps()
    {
        return $this->belongsTo(GoSessionStep::class, 'go_session_step_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function campaignSeason()
    {
        return $this->belongsTo(CampaignsSeason::class, 'campaign_season_id');
    }
    public function session()
    {
        return $this->belongsTo(GoSession::class, 'go_session_id');
    }
    public function attempts()
    {
        return $this->hasMany(SurvayFeedbackAttempt::class, 'survey_feedback_id');
    }
}
