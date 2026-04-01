<?php

namespace App\Services\Admin;

use App\Models\Company;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\CompanyAdminInvitation;
use App\Models\CampaignsSeason;
use Carbon\Carbon;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Mode;
use App\Models\CompanyDepartment;
use Illuminate\Support\Facades\Auth;
use App\Models\UserScore;
use Spatie\Permission\Models\Role;

class CompanyService
{
    use AppCommonFunction;

    public function getCompanies()
    {
        $query = Company::query()->with(['admin', 'mode'])->withCount('users');
        return $this->getPaginatedData($query);
    }
    public function createCompany($data, $admin)
    {
        DB::beginTransaction();

        try {
            $company = $this->createCompanyRecord($data, $admin);
            $user_result = $this->createUser($data, $company);
            $this->createDepartments($data, $company, 'admin');
            $this->assignCompanyAdminRoleToUser($user_result['user']);

            DB::commit();
            $this->sendInvitationEmail($user_result, $company, $user_result['is_new']);

            return $company;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function createCompanyRecord($data, Admin $admin)
    {
        return  Company::create([
            'created_by' => $admin->id,
            'mode_id' => $data['type'],
            'name' => $data['name'],
            'code' => $this->generateCode($data['name']),
            'registration_date' =>  Carbon::parse($data['registration_date']),
            'email' => $data['email'],
            'address' => $data['address'],
            'status' => config('constants.STATUS.ACTIVE'),
            'image' => $data['image'] ?? null
        ]);
    }

    protected function createUser($data, Company $company)
    {
        $plain_password = Str::random(12);
        $user = User::create([
            'first_name' => explode(' ', $data['name'])[0],
            'last_name' => explode(' ', $data['name'])[1] ?? null,
            'email' => $data['email'],
            'work_email' => $data['email'],
            'username' => $this->generateUsername($data['email']),
            'password' => Hash::make($plain_password),
            'company_id' => $company->id,
            'status' => config('constants.STATUS.ACTIVE'),
            'is_admin' => true
        ]);
        return [
            'user' => $user,
            'is_new' => true,
            'plain_password' => $plain_password
        ];
    }
    protected function sendInvitationEmail($user_result, $company, $is_new_user)
    {
        try {
            $password = $is_new_user ? $user_result['plain_password'] : null;
            $this->sendEmail(
                $user_result['user']->email,
                new CompanyAdminInvitation($user_result['user'], $company, $is_new_user, $password)
            );
        } catch (\Exception $e) {
            Log::error("Failed to send invitation email: {$e->getMessage()}");
        }
    }

    protected function assignCompanyAdminRoleToUser($user)
    {
        $user->assignRole('Company Admin');
        $permissions = $this->getPermissionsByRole('Company Admin', 'web');
        $user->syncPermissions($permissions);
    }

    protected function getPermissionsByRole($role_name, $guard_name)
    {
        $role = Role::where('name', $role_name)
                ->where('guard_name', $guard_name)
                ->first();

        if ($role) {
            return $role->permissions;
        }

        return collect();
    }

    public function findById($id)
    {
        $company = Company::find($id);
        if (!$company) {
            throw new \Exception('Company not found.');
        }
        return $company;
    }
    public function updateCompany($data, $company)
    {
        DB::beginTransaction();

        try {
            $updateData = [
                'name' => $data['name'],
                'address' => $data['address'],
                'registration_date' => Carbon::parse($data['registration_date']),
                'mode_id' => $data['type'],
            ];

            // Add image to update data if it exists
            if (isset($data['image'])) {
                $updateData['image'] = $data['image'];
            }

            $company->update($updateData);
            DB::commit();
            return $company;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteCompany($id)
    {
        DB::beginTransaction();
        try {
            $company = $this->findById($id);
            // Remove company association from employees (users with is_admin = 0)
            User::where('company_id', $company->id)
                ->where('is_admin', 0)
                ->update([
                    'company_id' => null,
                    'company_department_id' => null
                ]);

            // First delete related user scores
            UserScore::whereIn('campaign_season_id', function($query) use ($id) {
                $query->select('id')
                    ->from('campaigns_seasons')
                    ->where('company_id', $id);
            })->delete();

            // Then delete campaign seasons
            CampaignsSeason::where('company_id', $id)->delete();

            // Finally delete the company
            Company::findOrFail($id)->delete();

            DB::commit();
            return $company;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getCompanyDetails($id){
        return Company::with(['admin', 'mode'])->withCount('users')->find($id);
    }

    public function getCompanyMode($is_global = true)
    {
        return Mode::where('is_global', $is_global)->get();
    }

    protected function createDepartments($data, $company, $guard = 'web')
    {
        $departments = [];
        foreach ($data['department'] as $department) {
            $departments[] = [
                'company_id' => $company->id,
                'name' => $department,
                'status' => config('constants.STATUS.ACTIVE'),
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => $guard === 'web' ? Auth::guard($guard)->id() : null,
                'admin_id'   => $guard === 'admin' ? Auth::guard($guard)->id() : null,
            ];
        }
        CompanyDepartment::insert($departments);
    }

    public function getCompanyUsers($company, $search = null)
    {
        $users = $company->users();
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

    public function getCompanyEmployees($company, $search = null)
    {
        $query = $company->users()->where('is_admin', 0);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%");
            });
        }
        return $this->getPaginatedData($query);
    }

    public function getEmployeesCount($company_id)
    {
        return User::where('company_id', $company_id)->where('is_admin', 0)->count();
    }
}
