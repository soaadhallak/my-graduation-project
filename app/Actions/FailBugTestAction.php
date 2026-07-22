<?php

namespace App\Actions;

use App\Enums\BugHistoryTypes;
use App\Enums\BugStatuses;
use App\Models\Bug;
use App\Models\User;
use App\Services\BugPushNotificationService;
use App\Traits\TracksBugHistory;
use Illuminate\Support\Facades\DB;

class FailBugTestAction
{
    use TracksBugHistory;

    public function __construct(
        protected BugPushNotificationService $bugPushNotificationService
    ) {}

    public function execute(Bug $bug, string $reason, User $tester): Bug
    {
        return DB::transaction(function () use ($bug, $reason, $tester) {
            $originalStatus = $bug->status instanceof BugStatuses ? $bug->status->value : (string) $bug->status;
            $targetStatus = BugStatuses::CHANGES_REQUESTED->value;

            $bug->update([
                'status' => $targetStatus,
            ]);

            $this->transitionBug($bug, $originalStatus, $tester, $targetStatus, BugHistoryTypes::STATUS_CHANGE->value);

            $this->bugPushNotificationService->notifyQaFailed($bug);

            return $bug;
        });
    }
}
