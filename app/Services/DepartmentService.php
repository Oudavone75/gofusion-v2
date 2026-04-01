<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\CompanyDepartment;
use App\Models\Company;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\Log;

class DepartmentService
{
    use AppCommonFunction;

    public function __construct(private CompanyDepartment $company_department) {}

    public function getDepartments($query = null)
    {
        if (!$query) {
            $query = CompanyDepartment::with(['company', 'createdBy', 'createdByAdmin']);
        }

        return $this->getPaginatedData($query);
    }
    public function create($request, $company_id, $guard)
    {
        try {
            $this->company_department::create([
                'company_id' => $company_id,
                'name'       => $request['name'],
                'created_by' => $guard === 'web' ? Auth::guard($guard)->id() : null,
                'admin_id'   => $guard === 'admin' ? Auth::guard($guard)->id() : null,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }

    public function update($department, array $data)
    {
        return $department->update($data);
    }

    public function delete($department)
    {
        return $department->delete();
    }

    public function getDepartment($id)
    {
        return CompanyDepartment::with('company')->findOrFail($id);
    }

    public function getCompanies()
    {
        return $this->getAllCompanies();
    }

    public function getdepartmentUsers($department, $search = null)
    {
        $users = $department->company->users()->where('company_department_id', $department->id);
        if ($search) {
            $users->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('job_title', 'like', "%{$search}%")
                    ->orWhereHas('department', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('company.mode', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }
        return $this->getPaginatedData($users);
    }
}
