<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Otp;
use App\Traits\OTPTrait;

class ResendOTPService
{
    use OTPTrait;

    public function reSendOTP(string $email, string $type = 'forgot_password'):array
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ['success' => false, 'message' => trans('general.user_not_found')];
        }
        Otp::where('user_id', $user->id)->where('type', $type)->delete();
        $otp = $this->generateOtp();
        $this->createOtp($user, $otp,$type);
        $this->sendOtpMail($user, $otp);
        return ['success' => true, 'message' => trans('general.otp_sent')];
    }
}
