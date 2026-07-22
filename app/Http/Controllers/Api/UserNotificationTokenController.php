<?php

namespace App\Http\Controllers\Api;

use App\Data\UserNotificationTokenData;
use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyUserNotificationTokenRequest;
use App\Http\Requests\StoreUserNotificationTokenRequest;
use App\Http\Resources\UserNotificationTokenResource;
use App\Services\NotificationTokenService;
use Illuminate\Http\JsonResponse;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;
use Symfony\Component\HttpFoundation\Response;

class UserNotificationTokenController extends Controller
{
    public function __construct(
        protected NotificationTokenService $notificationTokenService
    ) {}

    public function store(StoreUserNotificationTokenRequest $request): JsonResponse
    {
        $userNotificationToken = $this->notificationTokenService->store(
            UserNotificationTokenData::from($request->validated()),
            $request->user()
        );

        return UserNotificationTokenResource::make($userNotificationToken)
            ->additional([
                'message' => ResponseMessages::CREATED->message()
            ])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
