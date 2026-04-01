<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

class ChallengeStep extends Model
{
    protected $fillable = [
        'campaign_id',
        'theme_id',
        'user_id',
        'company_id',
        'department_id',
        'challenge_category_id',
        'go_session_step_id',
        'title',
        'description',
        'guideline_text',
        'status',
        'points',
        'attempted_points',
        'is_global',
        'image_path',
        'video_url',
        'mode'
    ];

    const STATUS = [
        'PENDING'  => 'pending',
        'REJECTED' => 'rejected',
        'APPROVED' => 'approved'
    ];

    const IMAGE_PATH = "inspiration-challenges";

    protected function imagePath(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value
                ? (isUrl($value) ? $value : asset('storage/' . $value))
                : null,
        );
    }

    public function department()
    {
        return $this->belongsTo(CompanyDepartment::class);
    }

    public function departments()
    {
        return $this->belongsToMany(CompanyDepartment::class, 'challenge_departments', 'challenge_step_id', 'company_department_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class, 'theme_id');
    }

    public function category()
    {
        return $this->belongsTo(ChallengeCategory::class, 'challenge_category_id');
    }

    public function campaign()
    {
        return $this->belongsTo(CampaignsSeason::class, 'campaign_id');
    }

    public function challengePoints()
    {
        return $this->hasMany(ChallengePoint::class, 'challenge_step_id');
    }

    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('status', '=', $this::STATUS['APPROVED']);
    }

    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', '=', $this::STATUS['PENDING']);
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function event()
    {
        return $this->morphOne(Event::class, 'eventable')->withDefault();
    }
}
