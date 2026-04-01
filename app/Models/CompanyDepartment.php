<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDepartment extends Model
{
    protected $guarded = ['id', '_token'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function campaignSeasons()
    {
        return $this->hasMany(CampaignsSeason::class, 'company_department_id');
    }

    public function campaigns()
    {
        return $this->belongsToMany(CampaignsSeason::class, 'campaign_departments', 'company_department_id', 'campaign_id');
    }

    public function challenges()
    {
        return $this->belongsToMany(ChallengeStep::class, 'challenge_departments', 'company_department_id', 'challenge_step_id');
    }

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'notification_departments', 'company_department_id', 'notification_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function createdByAdmin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function getHasActiveCampaignsAttribute()
    {
        // Check campaigns assigned to this company directly (no department)
        $hasCompanyCampaigns = $this->campaignSeasons()
            ->wherePivotNull('company_department_id')
            ->where('status', 'active')
            ->exists();

        if ($hasCompanyCampaigns) {
            return true;
        }

        // Check campaigns assigned to any of this company's departments
        $departmentIds = $this->departments()->pluck('id');

        $hasDepartmentCampaigns = $this->campaignSeasons()
            ->wherePivotIn('company_department_id', $departmentIds)
            ->where('status', 'active')
            ->exists();

        return $hasDepartmentCampaigns;
    }

    public function users()
    {
        return $this->hasMany(User::class, 'company_department_id');
    }
}
