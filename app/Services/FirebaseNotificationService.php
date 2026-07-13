<?php

namespace App\Services;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

class FirebaseNotificationService
{
    public function send(
        string $token,
        string $title,
        string $body
    ): array {

        $credentials = new ServiceAccountCredentials(
            [
                'https://www.googleapis.com/auth/firebase.messaging'
            ],
            config('services.firebase.credentials')
        );

        $accessToken = $credentials->fetchAuthToken()['access_token'];

        $projectId = json_decode(
            file_get_contents(config('services.firebase.credentials')),
            true
        )['project_id'];


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


        return $response->json();
    }
}