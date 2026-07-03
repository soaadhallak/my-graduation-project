<?php
namespace App\Actions;

use App\Enums\BugHistoryTypes;
use App\Models\Bug;
use App\Models\User;
use App\Enums\BugStatuses;
use App\Traits\TracksBugHistory;
use Illuminate\Support\Facades\DB;

class PassBugTestAction
{
    use TracksBugHistory; 

    public function execute(Bug $bug, User $tester): Bug
    {
        return DB::transaction(function () use ($bug, $tester) {
            $originalStatus = $bug->status instanceof BugStatuses ? $bug->status->value : (string) $bug->status;
            $targetStatus = BugStatuses::CLOSED->value;

            $bug->update([
                'status' => $targetStatus,
            ]);

            $this->transitionBug($bug, $originalStatus, $tester, $targetStatus, BugHistoryTypes::STATUS_CHANGE->value);

            return $bug;
        });
    }
}