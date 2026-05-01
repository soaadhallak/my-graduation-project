<?php
namespace App\Actions;

use App\Data\PasswordData;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordAction
{
    public function execute(PasswordData $data)
    {
        $status = Password::sendResetLink($data->toArray());

        if ( $status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)]
            ]);    
        }

        return __($status);
    }
}