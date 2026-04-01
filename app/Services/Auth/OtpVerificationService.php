<?php

namespace App\Services\Auth;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OtpVerificationService
{
    public function verifyOtp(string $email, string $otp, string $otpType='forgot_password'): array
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ['success' => false, 'message' => trans('general.user_not_found')];
        }

        $otpRecord = Otp::where('user_id', $user->id)->where('type', $otpType)->latest()->first();

        if (!$otpRecord) {
            return ['success' => false, 'message' => trans('general.otp_not_found')];
        }

        if ($otpRecord->isExpired()) {
            return ['success' => false, 'message' => trans('general.otp_expired')];
        }

        if (!Hash::check($otp, $otpRecord->otp)) {
            return ['success' => false, 'message' => trans('general.invalid_otp')];
        }
        if($otpType == 'email_verification'){
            $user->email_verified_at = now();
            $user->save();
        }
        $otpRecord->delete();
        return ['success' => true, 'message' => trans('general.otp_verified')];
    }
}
