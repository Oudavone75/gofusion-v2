<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\WithdrawalRequestService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalRequestController extends Controller
{
    use ApiResponse;
    public function __construct(private WithdrawalRequestService $withdrawal_request_service)
    {
        $this->withdrawal_request_service = $withdrawal_request_service;
    }

    public function createWithdrawalRequest(Request $request)
    {
        try {
            $validated_data = $request->validate([
                'amount' => 'required|min:1|numeric',
                'withdrawal_purpose' => "required|in:Don exclusivement,éthi'Kdo",
            ], [
                'withdrawal_purpose.in' => "The selected withdrawal purpose is invalid, it must be Don exclusivement or éthi'Kdo"
            ]);
            $user = Auth::user();
            DB::beginTransaction();
            $response = $this->withdrawal_request_service->storeWithdrawalRequest($validated_data, $user);
            DB::commit();
            if ($response['success'] === false) {
                return $this->error(status: false, message: $response['message']);
            }
            return $this->success(status: true, message: $response['message'], result: $response['data'], code: 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error(status: false, message: $th->getMessage());
        }
    }

    public function getWithdrawalRequests()
    {
        try {
            $user = Auth::user();
            $response = $this->withdrawal_request_service->getWithdrawalRequests($user);
            if ($response['success'] === false) {
                return $this->error(status: false, message: $response['message'], result: [], code: 200, is_object: true);
            }
            return $this->success(status: true, message: $response['message'], result: $response['data'], code: 200);
        } catch (\Throwable $th) {
            return $this->error(status: false, message: $th->getMessage());
        }
    }
}
