<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageSubmissionGuideline extends Model
{
    protected $guarded = ['id', '_token'];

    protected $casts = [
        'keywords' => 'array',
    ];

    public function goSessionStep()
    {
        return $this->belongsTo(GoSessionStep::class);
    }

    public function attempts()
    {
        return $this->hasMany(ImageSubmissionStep::class, 'go_session_step_id', 'go_session_step_id');
    }

    public function appealingAttempts()
    {
        return $this->hasMany(ImageSubmissionStep::class, 'go_session_step_id', 'go_session_step_id')->where('status', 'appealing');
    }
}
