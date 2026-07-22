<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate($request->input('perPage', 15));

        return NotificationResource::collection($notifications)
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message(),
            ]);
    }

    public function markAsRead(Request $request, string $id): NotificationResource|JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (! $notification) {
            return response()->json([
                'message' => 'Notification not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $notification->markAsRead();

        return NotificationResource::make($notification)
            ->additional([
                'message' => ResponseMessages::UPDATED->message(),
            ]);
    }

    public function markAllAsRead(Request $request): AnonymousResourceCollection
    {
        $notifications = $request->user()->unreadNotifications;

        $notifications->markAsRead();

        return NotificationResource::collection($notifications)
            ->additional([
                'message' => ResponseMessages::UPDATED->message(),
            ]);
    }
}
