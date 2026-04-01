<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurvayFeedbackQuestionOption extends Model
{
    protected $guarded = ['id', '_token'];
    protected $table = 'survey_feedback_question_options';
}
