<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use App\Http\Requests\CompanyAdmin\LoginRequest;
use App\Services\AdminAuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\CompanyAdmin\ResetPasswordRequest;
use App\Traits\ApiResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\SubAdminRequest;
use App\Models\User;
use App\Traits\AppCommonFunction;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse, AppCommonFunction;
    public $admin_auth_service;
    public function __construct(AdminAuthService $admin_auth_service)
    {
        $this->admin_auth_service = $admin_auth_service;
    }

    public function loginView()
    {
        return view('company_admin.auth.login');
    }

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->first();
            $company_admin = $this->admin_auth_service->handleLogin($credentials, 'web');

            if ($user->status == 'inactive') {
                return back()->withInput($request->only('email'))->withErrors(['email' => 'Your account is deactivated.']);
            }

            if ($company_admin) {
                return redirect()->route('company_admin.dashboard');
            }
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Wrong email or password']);
        } catch (\Exception $e) {
            return back()->withInput($request->only('email'));
        }
    }

    public function logout()
    {
        Auth::guard('web')->logout();
        return redirect()->route('company_admin.login');
    }

    public function forgotPasswordView()
    {
        return view('company_admin.auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        $status = $this->admin_auth_service->handleForgotPassword($request->email, 'users');

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['success' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function resetPasswordView()
    {
        return view('company_admin.auth.reset-password');
    }
    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = $this->admin_auth_service->handleResetPassword(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            'users'
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('company_admin.login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
    public function changePassword()
    {
        return view('company_admin.change-password.index');
    }

    public function updatePassword(ChangePasswordRequest $request)
    {
       try {
            $this->admin_auth_service->handleChangePassword(
                $request->password,
                'web'
            );
            return $this->success(status: true, message: 'Password updated successfully', code: 200);
        } catch (\Exception $e) {
            $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function getSubAdmins()
    {
        $sub_admins = $this->admin_auth_service->getCompanySubAdmins();
        return view('company_admin.sub-admins.index', compact('sub_admins'));
    }

    public function createSubAdmin()
    {
        $data = $this->getRolesPermissions('web');
        $roles = $data['roles'];
        $permissions = $data['permissions'];

        return view('company_admin.sub-admins.create', compact('roles', 'permissions'));
    }

    public function storeSubAdmin(SubAdminRequest $request)
    {
        try {
            $selected_role = $request->role;
            $role_slug = Str::slug($selected_role);
            $permissions = $request->input("permissions.{$role_slug}", []);

            $data = $request->all();
            $data['permissions'] = $permissions;

            $this->admin_auth_service->storeCompanySubAdmin($data);

            return response()->json([
                'success' => true,
                'message' => 'Sub Admin created successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function editSubAdmin($id)
    {
        try {
            $sub_admin = User::with(['roles', 'permissions'])->findOrFail($id);

            $excluded_roles = ['Admin', 'Company Admin', 'User'];
            $roles = Role::with('permissions')->whereNotIn('name', $excluded_roles)->where('guard_name', 'web')->get();
            $permissions = Permission::where('guard_name', 'web')->get();

            $userRole = $sub_admin->roles->first();

            $userDirectPermissions = $sub_admin->permissions->pluck('id')->toArray();

            return view('company_admin.sub-admins.edit', compact('sub_admin', 'roles', 'permissions', 'userRole', 'userDirectPermissions'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updateSubAdmin(SubAdminRequest $request, $id)
    {
        try {
            $this->admin_auth_service->updateCompanySubAdmin($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Sub Admin updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function showSubAdmin($id)
    {
        $sub_admin = $this->admin_auth_service->getCompanySubAdminById($id);
        return view('company_admin.sub-admins.view', compact('sub_admin'));
    }

    public function deleteSubAdmin($id)
    {
        try {
            $this->admin_auth_service->deleteSubAdmin($id);
            return response()->json([
                'success' => true,
                'message' => 'Sub Admin deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        try {
            $response = $this->admin_auth_service->toggleSubAdminStatus($request->all(), $id);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
