<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageSubmissionStep extends Model
{
    protected $guarded = ['id', '_token'];

    protected $casts = [
        'matched_concepts' => 'array',
    ];

    public function goSessionStep()
    {
        return $this->belongsTo(GoSessionStep::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
