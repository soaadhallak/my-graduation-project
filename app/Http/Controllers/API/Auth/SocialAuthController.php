<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Encryption\DecryptException;

class SocialAuthController extends Controller
{
    public function redirectToGitHub(): JsonResponse
    {
        $encryptedState = encrypt(['user_id' => Auth::id()]);

        $url = Socialite::driver('github')
            ->stateless()
            ->scopes(['repo', 'admin:repo_hook'])
            ->with(['state' => $encryptedState])
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    public function handleGitHubCallback(Request $request)
    {
        try {
            $decryptedData = decrypt($request->input('state'));
            $userId = $decryptedData['user_id'] ?? null;

            if (!$userId) {
                return response()->json(['error' => 'User context lost'], 400);
            }

            $githubUser = Socialite::driver('github')->stateless()->user();

            SocialAuthService::AddDataForUser($userId, $githubUser);

            return redirect('http://localhost:3000/settings?github=connected');
        } catch (DecryptException $e) {
            return response()->json(['error' => 'Security check failed: Invalid state'], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to link GitHub: ' . $e->getMessage()], 500);
        }
    }
}
