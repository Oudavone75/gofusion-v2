<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $guarded = ['id', '_token'];

    public function options()
    {
        return $this->hasMany(QuizQuestionOption::class, 'quiz_question_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

     public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
