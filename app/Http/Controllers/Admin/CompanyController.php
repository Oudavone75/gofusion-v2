<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyCreateRequest;
use App\Http\Requests\Admin\CompanyUpdateRequest;
use App\Services\Admin\CompanyService;
use App\Services\CompanyJoinTokenService;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Traits\ApiResponse;

class CompanyController extends Controller
{
    use ApiResponse;
    public function __construct(
        private CompanyService $company_service,
        private CompanyJoinTokenService $join_token_service
    ) {}

    public function index(Request $request)
    {
        $companies = $this->company_service->getCompanies();
        return view('admin.company.index', compact('companies'));
    }
    public function create()
    {
        $company_modes = $this->company_service->getCompanyMode(false);
        return view('admin.company.create', compact('company_modes'));
    }

    public function store(CompanyCreateRequest $request)
    {
        try {
            $admin = auth('admin')->user();
            $validated_data = $request->validated();
            if ($request->hasFile('image')) {
                $filename = uploadFile($request->file('image'), 'public', 'company-logos');
                if (!$filename) {
                    return $this->error(status: false, message: 'Failed to upload image.', code: 500);
                }
                $validated_data['image'] = asset('storage/company-logos/' . $filename);
            }
            $this->company_service->createCompany($validated_data, $admin);
            return response()->json([
                'success' => true,
                'message' => 'Company created successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $company = $this->company_service->findById($id);
            $company_modes = $this->company_service->getCompanyMode(false);
            return view('admin.company.edit', compact('company', 'company_modes'));
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
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
            return response()->json([
                'success' => true,
                'message' => 'Company updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function hasActiveCampaigns(Company $company)
    {
        try {
            $company_actvive_campaign = $company->has_active_campaigns;
            return response()->json([
                'has_active_campaigns' => $company_actvive_campaign
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $company = $this->company_service->deleteCompany($id);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Company deleted successfully!'
                ]);
            }
            return redirect()->route('admin.company.index')
                ->with('success', 'Company deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $company = $this->company_service->getCompanyDetails($id);
        return view('admin.company.view', compact('company'));
    }

    public function companyUsers(Company $company, Request $request)
    {
        $users = $this->company_service->getCompanyUsers($company, $request->search);
        if ($request->ajax()) {
            return view('admin.company.company-users', compact('users', 'company'))->render();
        }
        return view('admin.company.company-users', compact('users', 'company'));
    }

    public function generateJoinToken(Request $request, Company $company)
    {
        try {
            $request->validate([
                'label' => 'nullable|string|max:255',
                'expiry' => 'required|string',
                'usage_limit' => 'required|integer|min:1',
            ]);

            $admin = auth('admin')->user();
            $token = $this->join_token_service->generateToken(
                $company->id,
                $request->label,
                $request->expiry ?? '7d',
                $admin->id,
                'admin',
                $request->usage_limit
            );

            return response()->json([
                'success' => true,
                'message' => 'Join token generated successfully!',
                'data' => [
                    'id' => $token->id,
                    'token' => $token->token,
                    'url' => env('WEB_APP_URL') . '/join?token=' . $token->token,
                    'label' => $token->label,
                    'expires_at' => $token->expires_at?->format('M d, Y H:i'),
                    'status' => $token->status,
                    'usage_limit' => $token->usage_limit,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function revokeJoinToken($tokenId)
    {
        try {
            $this->join_token_service->revokeToken($tokenId);
            return response()->json([
                'success' => true,
                'message' => 'Join token revoked successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getJoinTokens(Company $company)
    {
        try {
            $tokens = $this->join_token_service->getTokensForCompany($company->id);
            return response()->json([
                'success' => true,
                'data' => $tokens
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
