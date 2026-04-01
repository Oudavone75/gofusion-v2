<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpinWheelSubmissionStep extends Model
{
    protected $guarded = ['id', '_token'];
    protected $fillable = [
        'spin_wheel_id',
        'go_session_step_id',
        'user_id',
        'bonus_type',
        'points',
    ];

    public function spinwheel()
    {
        return $this->belongsTo(SpinWheel::class, 'spin_wheel_id', 'id');
    }

    // In SpinwheelAttempt.php
    public function getBonusValueAttribute()
    {
        return match ($this->bonus_type) {
            'bonus_leaves' => $this->spinwheel?->bonus_leaves,
            'promo_codes'  => $this->spinwheel?->promo_codes,
            'video_url'    => $this->spinwheel?->video_url,
            default        => null,
        };
    }

    public function goSessionStep()
    {
        return $this->belongsTo(GoSessionStep::class);
    }

}
