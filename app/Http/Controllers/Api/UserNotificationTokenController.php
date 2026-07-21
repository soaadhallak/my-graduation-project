<?php

namespace App\Http\Controllers\Api;

use App\Data\UserNotificationTokenData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserNotificationTokenRequest;
use App\Services\NotificationTokenService;
use Illuminate\Http\JsonResponse;

class UserNotificationTokenController extends Controller
{
    public function __construct(
        protected NotificationTokenService $notificationTokenService
    ) {}

    public function store(StoreUserNotificationTokenRequest $request): JsonResponse
    {
        $token = $this->notificationTokenService->store(
            UserNotificationTokenData::from($request->validated()),
            $request->user()
        );

        return response()->json([
            'message' => 'Notification token stored successfully.',
            'data' => $token,
        ]);
    }
}