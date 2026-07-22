<?php

namespace App\Services;

use App\Models\UserNotificationToken;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected ?string $accessToken = null;

    protected ?string $projectId = null;

    public function send(
        string $token,
        string $title,
        string $body
    ): array {
        $credentialsPath = config('services.firebase.credentials');

        if (! is_readable($credentialsPath)) {
            Log::error('Firebase credentials file is missing or unreadable.', [
                'path' => $credentialsPath,
            ]);

            return ['error' => 'Firebase credentials missing.'];
        }

        $accessToken = $this->getAccessToken($credentialsPath);
        $projectId = $this->getProjectId($credentialsPath);

        $response = Http::withToken($accessToken)
            ->post(
                "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'webpush' => [
                            'notification' => [
                                'icon' => '/favicon.ico',
                            ],
                        ],
                    ],
                ]
            );

        $payload = $response->json() ?? [];

        if ($this->isInvalidTokenResponse($response, $payload)) {
            UserNotificationToken::where('token', $token)->delete();
        }

        return $payload;
    }

    protected function getAccessToken(string $credentialsPath): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $credentialsPath
        );

        $this->accessToken = $credentials->fetchAuthToken()['access_token'];

        return $this->accessToken;
    }

    protected function getProjectId(string $credentialsPath): string
    {
        if ($this->projectId) {
            return $this->projectId;
        }

        $this->projectId = json_decode(
            file_get_contents($credentialsPath),
            true
        )['project_id'];

        return $this->projectId;
    }

    protected function isInvalidTokenResponse(Response $response, array $payload): bool
    {
        $errorCode = data_get($payload, 'error.details.0.errorCode')
            ?? data_get($payload, 'error.status');

        if (in_array($errorCode, ['UNREGISTERED', 'NOT_FOUND'], true)) {
            return true;
        }

        return $response->status() === 404;
    }
}
