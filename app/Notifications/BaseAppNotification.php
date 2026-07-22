<?php

namespace App\Notifications;

use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

abstract class BaseAppNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    abstract public function type(): string;

    abstract public function title(): string;

    abstract public function message(): string;

    /**
     * Extra payload stored in the database notification.
     *
     * @return array<string, mixed>
     */
    abstract protected function data(): array;

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'type' => $this->type(),
            'title' => $this->title(),
            'message' => $this->message(),
        ], $this->data());
    }

    /**
     * @return array{type: string, title: string, body: string}
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'type' => $this->type(),
            'title' => $this->title(),
            'body' => $this->message(),
        ];
    }
}
