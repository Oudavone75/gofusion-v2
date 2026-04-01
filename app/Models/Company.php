<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = ['id', '_token'];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }
    public function mode()
    {
        return $this->belongsTo(Mode::class)->withDefault();
    }

    public function companyAdmin()
    {
        return $this->hasOne(User::class, 'company_id')->whereHas('roles', function ($q) {
            $q->where('name', 'company_admin');
        });
    }

    public function departments()
    {
        return $this->hasMany(CompanyDepartment::class, 'company_id');
    }
    public function campaignSeasons()
    {
        return $this->hasMany(CampaignsSeason::class, 'company_id');
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

    public function challengeStep()
    {
        return $this->hasMany(ChallengeStep::class, 'company_id');
    }

    public function joinTokens()
    {
        return $this->hasMany(CompanyJoinToken::class, 'company_id');
    }
}
