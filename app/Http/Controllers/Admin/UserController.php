<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class UserController extends Controller
{
    use ApiResponse;
    public $user_service;

    public function __construct(UserService $user_service)
    {
        $this->user_service = $user_service;
    }

    public function index(Request $request)
    {
        $citizens = $this->user_service->getCitizens($request->search);

        if ($request->ajax()) {
            return view('admin.citizens.index', compact('citizens'))->render();
        }

        return view('admin.citizens.index', compact('citizens'));
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

    public function profile()
    {
        return view('admin.profile.index');
    }
    public function update(UpdateAdminRequest $request)
    {
        try {
            $admin = auth('admin')->user();
            $validated_data = $request->validated();
            if ($request->hasFile('image')) {
                if (!empty($admin->image_path)) {
                    $oldImagePath = public_path('storage/admin-logos/' . basename($admin->image_path));
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $filename = uploadFile($request->file('image'), 'public', 'admin-logos');
                if (!$filename) {
                    return $this->error(status: false, message: 'Failed to upload image.', code: 500);
                }
                $validated_data['image_path'] = asset('storage/admin-logos/' . $filename);
            }
            $this->user_service->updateAdmin($validated_data, $admin);
            $this->success(status: true, message: 'Admin profile updated successfully!', code: 200);
        } catch (\Exception $e) {
            return $this->error(status: false, message: $e->getMessage(), code: 500);
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
