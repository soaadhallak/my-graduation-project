<?php

namespace App\Notifications\Channels;

use App\Jobs\SendFirebaseNotificationJob;
use Illuminate\Notifications\Notification;

class FcmChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toFcm')) {
            return;
        }

        $message = $notification->toFcm($notifiable);

        if (empty($message['title']) || empty($message['body'])) {
            return;
        }

        SendFirebaseNotificationJob::dispatch(
            $notifiable->getKey(),
            $message['title'],
            $message['body'],
            $message['type'] ?? 'generic'
        );
    }
}
