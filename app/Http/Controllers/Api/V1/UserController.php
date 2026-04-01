<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(private UserService $user_service) {}


    public function getUserProgress(Request $request)
    {
        try {
            $response = $this->user_service->getUserStats($request->all());
            return $this->success(true, $response['message'], $response['data']);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function getMentionUsersList(Request $request)
    {
        try {
            $response = $this->user_service->getUsersList($request->all());
            return $this->success(true, $response['message'], $response['data']);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function getInviteFriendsList()
    {
        try {
            $user = Auth::user();
            $invite_friends = $this->user_service->getInviteFriendsList($user);
            return $this->success(status: true, message: 'Invite friends fetched successfully.', result: $invite_friends);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function encryptId(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $encrypted_id = Crypt::encrypt($request->input('id'));
            return $this->success(true, 'ID encrypted successfully.', ['encrypted_id' => $encrypted_id]);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function decryptId(Request $request)
    {
        try {
            $request->validate([
                'encrypted_id' => 'required',
            ]);
            $decrypted_id = Crypt::decrypt($request->input('encrypted_id'));
            return $this->success(true, 'ID decrypted successfully.', ['decrypted_id' => $decrypted_id]);
        } catch (\Throwable $e) {
            return $this->error(false, $e->getMessage());
        }
    }

    public function getUserLeavesList(Request $request)
    {
        try {
            $user = Auth::user();
            $month = $request->query('month') ?? now()->month;
            $year = $request->query('year') ?? now()->year;
            $request_data = [
                'month' => $month,
                'year' => $year,
            ];
            $leaves_list = $this->user_service->getUserLeavesList($user, $request_data);
            return $this->success(status: true, message: 'User leaves list fetched successfully.', result: $leaves_list);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }
}
