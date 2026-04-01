<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyFeedbackResponses extends Model
{
    protected $guarded = ['id', '_token'];

    public function question()
    {
        return $this->belongsTo(SurvayFeedbackQuestion::class, 'question_id');
    }

    public function option()
    {
        return $this->belongsTo(SurvayFeedbackQuestionOption::class, 'option_id');
    }
}
