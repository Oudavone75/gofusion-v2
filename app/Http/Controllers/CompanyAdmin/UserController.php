<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyUpdateRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Services\Admin\CompanyService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use App\Services\UserService;

class UserController extends Controller
{
    use ApiResponse;
    public function __construct(private CompanyService $company_service, private UserService $user_service) {}

    public function index()
    {
        $company_modes = $this->company_service->getCompanyMode(false);
        return view('company_admin.profile.index', compact('company_modes'));
    }

    public function update(CompanyUpdateRequest $request, Company $company)
    {
        try {
            $validated_data = $request->validated();
            if ($request->hasFile('image')) {
                if (!empty($company->image)) {
                    $oldImagePath = public_path('storage/company-logos/' . basename($company->image));
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $filename = uploadFile($request->file('image'), 'public', 'company-logos');
                if (!$filename) {
                    return $this->error(status: false, message: 'Failed to upload image.', code: 500);
                }
                $validated_data['image'] = asset('storage/company-logos/' . $filename);
            }
            $this->company_service->updateCompany($validated_data, $company);
            return $this->success(status: true, message: 'Company updated successfully!', code: 200);
        } catch (\Exception $e) {
            return $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function getEmployees(Request $request)
    {
        $company_id = Auth::user()->company_id;
        $company = $this->company_service->findById($company_id);
        $employees = $this->company_service->getCompanyEmployees($company, $request->search);
        if ($request->ajax()) {
            return view('company_admin.employees.index', compact('employees'))->render();
        }
        return view('company_admin.employees.index', compact('employees'));
    }

    public function toggleStatus(Request $request, $user_id)
    {
        try {
            $response = $this->user_service->toggleStatus($request->all(), $user_id);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($user_id)
    {
        try {
            $response = $this->user_service->deleteUser($user_id);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
