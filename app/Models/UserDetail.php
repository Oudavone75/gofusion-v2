<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $guarded = ['id', '_token'];

    public function sessionTimeDuration()
    {
        return $this->belongsTo(SessionTimeDuration::class, 'session_time_duration_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id')->withDefault();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
