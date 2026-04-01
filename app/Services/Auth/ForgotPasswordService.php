<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Traits\OTPTrait;

class ForgotPasswordService
{
    use OTPTrait;

    public function sendOTP(string $email):array
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ['success' => false, 'message' => trans('general.user_not_found')];
        }
        $otp = $this->generateOtp();
        $this->createOtp($user, $otp);
        $this->sendOtpMail($user, $otp);
        return ['success' => true, 'message' => trans('general.otp_sent')];
    }
}
