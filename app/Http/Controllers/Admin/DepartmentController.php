<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdateDepartmentRequest;
use App\Models\Company;
use App\Models\CompanyDepartment;
use App\Services\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(private DepartmentService $department_service) {}
    public function index(Request $request)
    {
        $query = CompanyDepartment::with(['company', 'createdBy', 'createdByAdmin']);
        if ($request->company_id) {
            $query->where('company_id', $request->company_id);
        }
        $company_departments = $this->department_service->getDepartments($query);
        $companies = $this->department_service->getCompanies();
        return view('admin.department.index', compact('company_departments', 'companies'));
    }

    public function create()
    {
        $companies = $this->department_service->getCompanies();
        return view('admin.department.create', compact('companies'));
    }

    public function store(StoreDepartmentRequest $request)
    {
        try {
            $company_id = $request->company_id;
            $this->department_service->create($request->validated(), $company_id, 'admin');
            return response()->json([
                'success' => true,
                'message' => 'Department created successfully!'
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
            $company_department = $this->department_service->getDepartment($id);
            return view('admin.department.edit', compact('company_department'));
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function update(UpdateDepartmentRequest $request, CompanyDepartment $company_department)
    {
        try {
            $this->department_service->update($company_department, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function hasDepartmentActiveCampaigns(CompanyDepartment $company_department)
    {
        try {
            $active_campaigns = $company_department->has_active_campaigns;
            return response()->json([
                'has_active_campaigns' => $active_campaigns
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(CompanyDepartment $company_department)
    {
        try {
            $this->department_service->delete($company_department);
            return redirect()->route('admin.department.index')
                ->with('success', 'Department deleted successfully!');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
