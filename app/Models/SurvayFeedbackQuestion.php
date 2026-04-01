<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurvayFeedbackQuestion extends Model
{
    protected $guarded = ['id', '_token'];
    protected $table = 'survey_feedback_questions';

    public function options()
    {
        return $this->hasMany(SurvayFeedbackQuestionOption::class, 'question_id');
    }
}
