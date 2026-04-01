<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Http\Requests\PendingCompanyAssignRequest;
use App\Services\CompanyService;
use App\Services\CompanyJoinTokenService;
use App\Services\UserScoreService;
use App\Http\Requests\CompanyDepartmentRequest;
use App\Http\Resources\CompanyResource;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    use ApiResponse;
    public function __construct(
        public CompanyService $company_service,
        public UserScoreService $user_score_service,
        public CompanyJoinTokenService $join_token_service
    ) {}

    public function codeVerification(Request $request)
    {
        try {
            $request->validate([
                'token' => 'required_without:code|string',
                'code' => 'required_without:token|string',
            ]);

            // Check for new secure Token first, then fallback to legacy Code
            $token = $request->token;
            $code = $request->code;

            if ($token) {
                $result = $this->join_token_service->resolveToken($token, Auth::user());
                if (!$result['success']) {
                    return $this->error(status: false, message: $result['message']);
                }

                $company = $result['data'];
                $companyResource = new CompanyResource($company, Auth::user()->company?->mode?->name);

                return $this->success(
                    status: true,
                    message: $result['message'],
                    result: $companyResource
                );
            }

            if ($code) {
                $result = $this->company_service->verifyCompanyCode($code);
                if (!$result['success']) {
                    return $this->error(status: false, message: $result['message']);
                }
                return $this->success(status: true, message: $result['message'], result: $result['data']);
            }

            return $this->error(status: false, message: 'Please provide a valid join link or code.');
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function getCompanydepartments(CompanyDepartmentRequest $request)
    {
        try {
            $departments = $this->company_service->getCompanydepartments($request->company_id);
            return $this->success(status: true, message: $departments['messages'], result: $departments['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function pendingCompanyAssignRequest(PendingCompanyAssignRequest $request)
    {
        try {
            $result = $this->company_service->pendingCompanyAssignRequest($request->work_email,auth()->user()->id);
            if(!$result['success']){
                return $this->error(status: false, message: $result['message']);
            }
            return $this->success(status: true, message: $result['message'], result: $result['data']);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

}
