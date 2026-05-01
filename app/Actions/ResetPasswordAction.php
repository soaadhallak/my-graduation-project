<?php

namespace App\Actions;

use App\Data\PasswordData;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ResetPasswordAction
{

    public function execute(PasswordData $passwordData): string
    {
        $status = Password::reset($passwordData->toArray(), function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->save();
        });

        if ($status != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return __($status);
    }
}
