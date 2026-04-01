<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinWheel extends Model
{
    protected $guarded = ['id', '_token'];

    public function goSessionStep()
    {
        return $this->belongsTo(GoSessionStep::class, 'go_session_step_id');
    }

    public function attempts()
    {
        return $this->hasMany(SpinWheelSubmissionStep::class, 'go_session_step_id', 'go_session_step_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
