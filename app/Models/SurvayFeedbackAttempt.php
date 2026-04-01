<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurvayFeedbackAttempt extends Model
{
    protected $guarded = ['id', '_token'];

    protected $table = 'survey_feedback_attempts';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function surveyFeedbackResponses()
    {
        return $this->hasMany(SurveyFeedbackResponses::class, 'survey_feedback_attempt_id');
    }

    public function goSessionStep()
    {
        return $this->belongsTo(GoSessionStep::class);
    }
}
