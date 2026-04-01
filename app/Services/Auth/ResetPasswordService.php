<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetPasswordService
{
    public function resetPassword(string $email, string $newPassword): array
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return ['success' => false, 'message' => trans('general.user_not_found')];
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return ['success' => true, 'message' => trans('general.password_reset')];
    }
}
