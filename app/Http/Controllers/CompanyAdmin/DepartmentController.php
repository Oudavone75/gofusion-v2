<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyAdmin\UpdateDepartmentRequest;
use App\Models\CompanyDepartment;
use Illuminate\Support\Facades\Auth;
use App\Services\DepartmentService;
use App\Http\Requests\CompanyAdmin\StoreDepartmentRequest;
use Symfony\Component\HttpFoundation\Request;

class DepartmentController extends Controller
{
    public function __construct(public DepartmentService $department_service) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = CompanyDepartment::with(['company', 'createdBy', 'createdByAdmin'])
            ->withCount('users')
            ->where('company_id', Auth::user()->company_id);
        $company_departments = $this->department_service->getDepartments($query);
        return view('company_admin.departments.index', compact('company_departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('company_admin.departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDepartmentRequest $request)
    {
        $company_id = Auth::user()->company_id;
        $this->department_service->create($request->validated(), $company_id, 'web');

        return redirect()->route('company_admin.departments.index')->with('success', 'Department created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($company_department)
    {
        $company_department = CompanyDepartment::find($company_department);
        if ($company_department->company_id !== Auth::user()->company_id) {
            abort(403);
        }

        return view('company_admin.departments.edit', compact('company_department'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDepartmentRequest $request, $company_department)
    {
        $company_department = CompanyDepartment::find($company_department);
        if ($company_department->company_id !== Auth::user()->company_id) {
            abort(403);
        }
        $this->department_service->update($company_department, $request->validated());

        return redirect()->route('company_admin.departments.index')->with('success', 'Updated successfully.');
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($company_department)
    {
        $company_department = CompanyDepartment::find($company_department);
        if ($company_department->company_id !== Auth::user()->company_id) {
            abort(403);
        }
        $this->department_service->delete($company_department);

        return redirect()->route('company_admin.departments.index')->with('success', 'Deleted successfully.');
    }

    public function departmentUsers(CompanyDepartment $department, Request $request)
    {
        $users = $this->department_service->getdepartmentUsers($department, $request->search);
        if ($request->ajax()) {
            return view('company_admin.departments.department-users', compact('users', 'department'))->render();
        }
        return view('company_admin.departments.department-users', compact('users', 'department'));
    }
}
