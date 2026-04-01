<?php

namespace App\Traits;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\OtpMail;

trait OTPTrait
{
    public function createOtp(User $user, string $otp,$type = 'forgot_password')
    {
        return Otp::create([
            'user_id' => $user->id,
            'otp' => Hash::make($otp),
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);
    }
    
    public function generateOtp()
    {
        return rand(100000, 999999);
    }

    public function sendOtpMail(User $user, string $otp, string $subject = 'Mot de passe oublié OTP')
    {
        Mail::to($user->email)->send(new OtpMail($otp,$subject,$user));
    }
}
