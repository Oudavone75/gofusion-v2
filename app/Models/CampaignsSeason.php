<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CampaignsSeason extends Model
{
    protected $guarded = ['id', '_token'];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function department()
    {
        return $this->belongsTo(CompanyDepartment::class, 'company_department_id');
    }

    public function departments()
    {
        return $this->belongsToMany(CompanyDepartment::class, 'campaign_departments', 'campaign_id', 'company_department_id');
    }

    public function  goSessions()
    {
        return $this->hasMany(GoSession::class, 'campaign_season_id');
    }

    public function getStartDateAttribute($value)
    {
        return date('d M Y', strtotime($value));
    }

    public function getEndDateAttribute($value)
    {
        return date('d M Y', strtotime($value));
    }

    public function setStartDateAttribute($value)
    {
        // $this->attributes['start_date'] = date('Y-m-d' );
        $this->attributes['start_date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function campaignsSeasonsRewardRanges()
    {
        return $this->hasMany(CampaignsSeasonsRewardRange::class, 'campaign_season_id');
    }
}
