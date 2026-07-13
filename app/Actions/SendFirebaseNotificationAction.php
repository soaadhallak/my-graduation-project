<?php

namespace App\Actions;

use App\Models\User;
use App\Services\FirebaseNotificationService;

class SendFirebaseNotificationAction
{
    public function __construct(
        protected FirebaseNotificationService $firebaseNotificationService
    ) {}

    public function execute(
        User $user,
        string $title,
        string $body
    ): array {

        $responses = [];

        foreach ($user->notificationTokens()->get() as $notificationToken) {

            $responses[] = $this->firebaseNotificationService->send(
                $notificationToken->token,
                $title,
                $body
            );
        }

        return $responses;
    }
}