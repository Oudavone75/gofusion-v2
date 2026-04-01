<?php

namespace App\Services;

use App\Mail\SubAdminCreatedMail;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;

class AdminAuthService
{
    use AppCommonFunction;

    public function handleLogin($credentials, $guard)
    {
        if (Auth::guard($guard)->attempt($credentials, false)) {
            app()->setLocale('en');
            return true;
        }
        return false;
    }

    public function handleForgotPassword($email, $user_type)
    {
        $status = Password::broker($user_type)->sendResetLink(
            ['email' => $email]
        );
        return $status;
    }

    public function handleResetPassword($credentials, $user_type)
    {
        $status = Password::broker($user_type)->reset(
            $credentials,
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status;
    }

    public function handleChangePassword($newPassword, $guard)
    {
        $user = Auth::guard($guard)->user();

        if (!$user) {
            throw new \Exception('User not authenticated');
        }
        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        return true;
    }

    public function getSubAdmins()
    {
        $query = Admin::where('email', '!=', 'admin@gofusion.com');

        return $this->getPaginatedData($query);
    }

    public function storeSubAdmin($data = [])
    {
        DB::beginTransaction();

        try {
            $sub_admin_data = $this->buildSubAdminUserData($data);
            $sub_admin = Admin::create($sub_admin_data);

            if (isset($data['role'])) {
                $sub_admin->assignRole($data['role']);
            }

            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $permissions = Permission::whereIn('id', $data['permissions'])
                    ->where('guard_name', 'admin')
                    ->get();

                $sub_admin->syncPermissions($permissions);
            }

            // $this->sendEmail(
            // $sub_admin->email,
            try {
                Mail::to($sub_admin->email)->send(new SubAdminCreatedMail($sub_admin, $data['password']));
            } catch (\Exception $e) {
                Log::error('Failed to send Sub Admin creation email: ' . $e->getMessage());
            }
            // );

            DB::commit();
            return $sub_admin;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create user. Please try again.');
        }
    }

    public function updateSubAdmin($id, $request = [])
    {
        DB::beginTransaction();

        try {
            $sub_admin = Admin::findOrFail($id);
            $sub_admin_data = $this->buildSubAdminUserData($request, $sub_admin);
            $sub_admin->update($sub_admin_data);

            $selected_role = $request['role'];
            $role_slug = Str::slug($selected_role);
            $permissions = $request["permissions"][$role_slug] ?? [];

            if (isset($request['role'])) {
                $sub_admin->syncRoles([$request['role']]);
            }

            if (isset($permissions) && is_array($permissions)) {
                $permissions = Permission::whereIn('id', $permissions)
                    ->where('guard_name', 'admin')
                    ->get();

                $sub_admin->syncPermissions($permissions);
            }

            DB::commit();
            return $sub_admin;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to update user. Please try again.');
        }
    }

    public function buildSubAdminUserData($request, $existingUser = null)
    {
        $data = [
            'name'  => $request['name'],
            'email' => isset($request['email']) ? $request['email'] : $existingUser->email,
        ];

        if (!empty($request['password'])) {
            $data['password'] = Hash::make($request['password']);
        }

        return $data;
    }

    public function getSubAdminById($id)
    {
        return Admin::findOrFail($id);
    }

    public function deleteSubAdmin($id)
    {
        $sub_admin = Admin::findOrFail($id);
        $sub_admin->delete();

        return true;
    }

    public function toggleStatus($request, $id)
    {
        $status = $request['status'];
        $sub_admin = Admin::find($id);

        $sub_admin->status = $status === 'active' ? true : false;
        $sub_admin->save();

        return [
            'success' => true,
            'message' => $sub_admin->status ? 'Sub Admin activated successfully' : 'Sub Admin deactivated successfully'
        ];
    }

    public function getCompanySubAdmins()
    {
        $query = User::query()
            ->where('company_id', auth('web')->user()->company_id)
            ->where('is_sub_admin', true);

        return $this->getPaginatedData($query);
    }

    public function storeCompanySubAdmin($data = [])
    {
        DB::beginTransaction();

        try {
            $sub_admin_data = $this->buildCompanySubAdminUserData($data);
            $sub_admin = User::create($sub_admin_data);

            if (isset($data['role'])) {
                $sub_admin->assignRole($data['role']);
            }

            if (isset($data['permissions']) && is_array($data['permissions'])) {
                $permissions = Permission::whereIn('id', $data['permissions'])
                    ->where('guard_name', 'web')
                    ->get();

                $sub_admin->syncPermissions($permissions);
            }

            $this->sendEmail(
                $sub_admin->email,
                new SubAdminCreatedMail($sub_admin, $data['password'])
            );

            DB::commit();
            return $sub_admin;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create user. Please try again.');
        }
    }

    public function buildCompanySubAdminUserData($request, $existingUser = null)
    {
        $data = [
            'first_name'  => $request['name'],
            'email' => isset($request['email']) ? $request['email'] : $existingUser->email,
            'work_email' => isset($request['email']) ? $request['email'] : $existingUser->email,
            'username' => isset($request['email']) ? extractUsernameFromEmail($request['email']) : $existingUser->username,
            'company_id' => auth('web')->user()->company_id,
            'is_sub_admin' => true,
        ];

        if (!empty($request['password'])) {
            $data['password'] = Hash::make($request['password']);
        }

        return $data;
    }

    public function updateCompanySubAdmin($id, $request = [])
    {
        DB::beginTransaction();

        try {
            $sub_admin = User::findOrFail($id);
            $sub_admin_data = $this->buildCompanySubAdminUserData($request, $sub_admin);
            $sub_admin->update($sub_admin_data);

            $selected_role = $request['role'];
            $role_slug = Str::slug($selected_role);
            $permissions = $request["permissions"][$role_slug] ?? [];

            if (isset($request['role'])) {
                $sub_admin->syncRoles([$request['role']]);
            }

            if (isset($permissions) && is_array($permissions)) {
                $permissions = Permission::whereIn('id', $permissions)
                    ->where('guard_name', 'web')
                    ->get();

                $sub_admin->syncPermissions($permissions);
            }

            DB::commit();
            return $sub_admin;
        } catch (\Exception $e) {
            dd($e->getMessage());
            DB::rollBack();
            throw new \Exception('Failed to update user. Please try again.');
        }
    }

    public function getCompanySubAdminById($id)
    {
        return User::findOrFail($id);
    }

    public function toggleSubAdminStatus($request, $user_id)
    {
        $status = $request['status'];
        $user = User::find($user_id);
        $user->status = $status === 'active' ? 'active' : 'inactive';
        $user->save();

        return [
            'success' => true,
            'message' => $user->status === 'active' ? 'Sub Admin activated successfully' : 'Sub Admin deactivated successfully'
        ];
    }
}
