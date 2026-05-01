<?php
namespace App\Services;

use App\Models\User;
use App\DTOs\Auth\SocialUserDTO;
use Illuminate\Support\Facades\Auth;

class SocialAuthService
{
    public static function AddDataForUser(string $userId,  $githubUser)
    {
        $user = User::findOrFail($userId);
        
        $user->update([
            'github_id'            => $githubUser->getId(),
            'github_token'         => $githubUser->token,
            'github_refresh_token' => $githubUser->refreshToken??null, 
        ]);
    }
}
