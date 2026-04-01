<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengePoint extends Model
{
    protected $guarded = ['id', '_token'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function challengeStep()
    {
        return $this->belongsTo(ChallengeStep::class, 'challenge_step_id');
    }
}
