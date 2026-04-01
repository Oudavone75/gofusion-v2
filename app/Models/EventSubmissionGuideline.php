<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSubmissionGuideline extends Model
{
    protected $guarded = ['id', '_token'];


    public function event()
    {
        return $this->morphOne(Event::class, 'eventable')->withDefault();
    }

    public function goSessionStep()
    {
        return $this->belongsTo(GoSessionStep::class);
    }

    public function attempts()
    {
        return $this->hasMany(EventSubmissionStep::class, 'go_session_step_id', 'go_session_step_id');
    }
}
