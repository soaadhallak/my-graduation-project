<?php

namespace App\Traits;

use App\Models\Bug;
use App\Models\User;

trait TracksBugHistory
{
    public function transitionBug(Bug $bug, string $fromState, User $user, string $toState, string $type, ?string $comment = null)
    {
        $bug->histories()->create([
            'user_id' => $user->id,
            'from_state' => $fromState,
            'type'    => $type,
            'to_state' => $toState,
            'notes' => $comment,
        ]);
    }
}
