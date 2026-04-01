<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminResetPasswordRequest;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\SubAdminRequest;
use Illuminate\Http\Request;
use App\Services\AdminAuthService;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use App\Models\Admin;
use App\Traits\AppCommonFunction;

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
        return view('admin.auth.login');
    }

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            $admin = Admin::where('email', $credentials['email'])->first();

            if (!$admin) {
                return back()->withInput($request->only('email'))->withErrors(['email' => 'These credentials do not match our records.']);
            }

            if (!$admin->status) {
                return back()->withInput($request->only('email'))->withErrors(['email' => 'Your account is deactivated.']);
            }

            $response = $this->admin_auth_service->handleLogin($credentials, 'admin');

            if ($response) {
                return redirect()->route('admin.dashboard');
            }

            return back()->withInput($request->only('email'))->withErrors(['email' => 'Invalid credentials.']);
        } catch (\Exception $e) {
            return back()->withInput($request->only('email'));
        }
    }

    public function forgotPasswordView()
    {
        return view('admin.auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admins,email',
        ]);
        $status = $this->admin_auth_service->handleForgotPassword($request->email, 'admins');

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['success' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function resetPasswordView()
    {
        return view('admin.auth.reset-password');
    }
    public function resetPassword(AdminResetPasswordRequest $request)
    {
        $status = $this->admin_auth_service->handleResetPassword(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            'admins'
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('admin.login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }

    public function changePassword()
    {
        return view('admin.change-password.index');
    }

    public function updatePassword(ChangePasswordRequest $request)
    {
        try {
            $this->admin_auth_service->handleChangePassword(
                $request->password,
                'admin'
            );
            return $this->success(status: true, message: 'Password updated successfully', code: 200);
        } catch (\Exception $e) {
            $this->error(status: false, message: $e->getMessage(), code: 500);
        }
    }

    public function getSubAdmins()
    {
        $sub_admins = $this->admin_auth_service->getSubAdmins();
        return view('admin.sub-admins.index', compact('sub_admins'));
    }

    public function createSubAdmin()
    {
        $data = $this->getRolesPermissions('admin');
        $roles = $data['roles'];
        $permissions = $data['permissions'];

        return view('admin.sub-admins.create', compact('roles', 'permissions'));
    }

    public function storeSubAdmin(SubAdminRequest $request)
    {
        try {
            $selected_role = $request->role;
            $role_slug = Str::slug($selected_role);
            $permissions = $request->input("permissions.{$role_slug}", []);

            $data = $request->all();
            $data['permissions'] = $permissions;

            $this->admin_auth_service->storeSubAdmin($data);

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
            $sub_admin = Admin::with(['roles', 'permissions'])->findOrFail($id);

            $this->getRolesPermissions('admin');
            $data = $this->getRolesPermissions('admin');
            $roles = $data['roles'];
            $permissions = $data['permissions'];

            $userRole = $sub_admin->roles->first();

            $userDirectPermissions = $sub_admin->permissions->pluck('id')->toArray();

            return view('admin.sub-admins.edit', compact('sub_admin', 'roles', 'permissions', 'userRole', 'userDirectPermissions'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updateSubAdmin(SubAdminRequest $request, $id)
    {
        try {
            $this->admin_auth_service->updateSubAdmin($id, $request->all());

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
        $sub_admin = $this->admin_auth_service->getSubAdminById($id);
        return view('admin.sub-admins.view', compact('sub_admin'));
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
            $response = $this->admin_auth_service->toggleStatus($request->all(), $id);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
