<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyContactRequest;
use App\Services\CompanyContactService;
use App\Traits\ApiResponse;

class CompanyContactController extends Controller
{
    use ApiResponse;
    public function __construct(private CompanyContactService $company_contact_service) {}

    public function saveCompanyContact(CompanyContactRequest $request)
    {
        try {
            $request->merge(['user_id' => auth()->id()]);
            $response = $this->company_contact_service->saveCompanyContact($request->all());
            if ($response['success'] === false) {
                return $this->error(status: false, message: $response['message'], code: 400);
            }
            return $this->success(status: true, message: $response['message'], result: $response['data']);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
        return $this->success(status: true, message: $response['message'], result: $response['data']);
    }
}
