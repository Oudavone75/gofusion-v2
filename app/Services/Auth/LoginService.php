<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Services\CarbonFootprintService;
use App\Traits\AppCommonFunction;

class LoginService
{
    use AppCommonFunction;
    public function __construct(private CarbonFootprintService $carbon_footprint_service) {}

    public function authenticate(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }
        $user = Auth::user();
        if ($user->status == 'inactive') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => trans('general.in_active_account'),
            ]);
        }

        if (is_null($user->email_verified_at)) {
            throw ValidationException::withMessages([
                'email' => trans('general.email_not_verified'),
            ])->status(423);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return [
            "user" => $user,
            'token' => $token
        ];
    }
}
