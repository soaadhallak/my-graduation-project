<?php

namespace App\Services;

use App\Data\UserNotificationTokenData;
use App\Models\User;
use App\Models\UserNotificationToken;
use Illuminate\Support\Facades\DB;

class NotificationTokenService
{
    public function store(UserNotificationTokenData $data, User $user): UserNotificationToken
    {
        return DB::transaction(function () use ($data, $user) {
            $this->detachTokenFromOtherDevices($data->token);

            $userNotificationToken = UserNotificationToken::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'device_name' => $data->device_name,
                ],
                [
                    'token' => $data->token,
                    'last_used_at' => now(),
                ]
            );

            return $userNotificationToken;
        });
    }

    public function update(UserNotificationTokenData $data, User $user): UserNotificationToken
    {
        return DB::transaction(function () use ($data, $user) {
            $userNotificationToken = $user->notificationTokens()
                ->where('device_name', $data->device_name)
                ->firstOrFail();

            $this->detachTokenFromOtherDevices($data->token, $userNotificationToken->id);

            $userNotificationToken->update([
                'token' => $data->token,
                'last_used_at' => now(),
            ]);

            return $userNotificationToken->fresh();
        });
    }

    public function delete(User $user, ?string $token = null, ?string $deviceName = null): int
    {
        $query = $user->notificationTokens();

        if ($token) {
            $query->where('token', $token);
        }

        if ($deviceName) {
            $query->where('device_name', $deviceName);
        }

        if (! $token && ! $deviceName) {
            return 0;
        }

        return $query->delete();
    }

    /**
     * FCM tokens are globally unique; remove any stale ownership before assigning.
     */
    protected function detachTokenFromOtherDevices(string $token, ?int $exceptId = null): void
    {
        $query = UserNotificationToken::where('token', $token);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        $query->delete();
    }
}
