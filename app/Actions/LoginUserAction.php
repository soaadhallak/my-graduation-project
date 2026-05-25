<?php

namespace App\Actions;

use App\Data\AcceptInvitationData;
use App\Data\UserData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{

    public function execute(UserData $userData, AcceptInvitationAction $acceptInvitationAction): array
    {
        $user = User::where('email', $userData->email)->first();

        if (!$user || !Hash::check($userData->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password is wrong'],
            ]);
        }

        if ($userData->token) {
            $acceptInvitationAction->execute($user, AcceptInvitationData::from(['token' => $userData->token]));
        }

        $token = $user->createToken('user-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }
}
