<?php

namespace App\Actions;

use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Log;

class SendFirebaseNotificationAction
{
    public function __construct(
        protected FirebaseNotificationService $firebaseNotificationService
    ) {}

    public function execute(
        User $user,
        string $title,
        string $body,
        string $type = 'generic'
    ): array {
        $responses = [];
        $recipient = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];

        foreach ($user->notificationTokens()->get() as $notificationToken) {
            try {
                $response = $this->firebaseNotificationService->send(
                    $notificationToken->token,
                    $title,
                    $body
                );

                $responses[] = $response;

                if (isset($response['name'])) {
                    Log::info('Firebase push succeeded', [
                        'type' => $type,
                        'title' => $title,
                        'to' => $recipient,
                    ]);
                } else {
                    Log::error('Firebase push failed', [
                        'type' => $type,
                        'title' => $title,
                        'to' => $recipient,
                        'error' => $response['error'] ?? $response,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Firebase push failed', [
                    'type' => $type,
                    'title' => $title,
                    'to' => $recipient,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $responses;
    }
}
