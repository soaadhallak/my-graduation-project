<?php

namespace App\Jobs;

use App\Actions\SendFirebaseNotificationAction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFirebaseNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        protected int $userId,
        protected string $title,
        protected string $body,
        protected string $type = 'generic'
    ) {}

    public function handle(SendFirebaseNotificationAction $sendFirebaseNotificationAction): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            Log::warning('Firebase push job skipped: user not found', [
                'type' => $this->type,
                'user_id' => $this->userId,
            ]);

            return;
        }

        $sendFirebaseNotificationAction->execute(
            $user,
            $this->title,
            $this->body,
            $this->type
        );
    }
}
