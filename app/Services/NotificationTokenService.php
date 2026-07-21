<?php

namespace App\Services;

use App\Data\UserNotificationTokenData;
use App\Models\User;
use App\Models\UserNotificationToken;

class NotificationTokenService
{
    public function store(UserNotificationTokenData $data, User $user): UserNotificationToken
    {
        return UserNotificationToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_name' => $data->device_name,
            ],
            [
                'token' => $data->token,
                'last_used_at' => now(),
            ]
        );
    }
}