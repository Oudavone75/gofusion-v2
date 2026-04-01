<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoUserProgress extends Model
{
    protected $fillable = [
        'campaigns_season_id',
        'go_session_id',
        'go_session_step_id',
        'user_id',
        'is_complete',
    ];

    public function session()
    {
        return $this->belongsTo(GoSession::class, 'go_session_id');
    }

    public function step()
    {
        return $this->belongsTo(GoSessionStep::class, 'go_session_step_id');
    }
}
