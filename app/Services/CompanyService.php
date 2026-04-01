<?php

namespace App\Services;

use App\Http\Resources\CompanyDepartmentResource;
use App\Http\Resources\CompanyResource;
use App\Models\User;
use App\Models\Company;
use App\Models\CampaignsSeason;
use App\Models\CompanyDepartment;
use Illuminate\Support\Facades\Auth;

class CompanyService
{
    public function verifyCompanyCode(string $code): array
    {
        $company = Company::with(['departments', 'mode'])->where('code', $code)->where('status', 'active')->first();
        if (!$company) {
            return ['success' => false, 'message' => trans('general.organization_not_found')];
        }

        $activeCampaign = CampaignsSeason::where('company_id', $company->id)
            ->whereIn('status', ['active', 'in-progress'])
            ->first();

        if ($activeCampaign) {
            $campaignDepartments = $activeCampaign->departments()
                ->select('company_departments.id', 'company_departments.name')
                ->get();
            $company->setRelation('departments', $campaignDepartments);
        }

        $companyResource = new CompanyResource($company, Auth::user()->company?->mode?->name);
        return ['success' => true, 'message' => trans('general.organization_found'), 'data' => $companyResource];
    }

    public function getCompanydepartments(int $companyId): array
    {
        $departments = CompanyDepartment::select('id', 'name')->where('company_id', $companyId)->where('status', 'active')->get();
        return ['success' => true, 'message' => trans('general.company_departments_fetched'), 'data' => $departments];
    }

    public function pendingCompanyAssignRequest($email, $user_id): array
    {
        $user = User::find($user_id);
        if (!$user) {
            return ['success' => false, 'message' => trans('general.user_not_found')];
        }
        $user->work_email = $email;
        $user->save();
        return ['success' => true, 'message' => trans('general.user_request_sent'), 'data' => $user];
    }

    public function getDepartments($company_id)
    {
        $departments = CompanyDepartment::where('company_id', $company_id)
            ->whereStatus('active')
            ->orderBy('name', 'ASC')->get();
        $list = CompanyDepartmentResource::collection($departments);
        return $list;
    }
}
