<?php

namespace App\Services;

use App\Data\BugSubmissionData;
use App\Enums\BugHistoryTypes;
use App\Enums\BugStatuses;
use App\Models\Bug;
use App\Models\BugHistory;
use App\Models\BugSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BugSubmissionsService
{
    public function store(BugSubmissionData $data, User $user): BugSubmission
    {
        $bug = Bug::findOrFail($data->bugId);

        return DB::transaction(function () use ($bug, $data, $user) {
            $originalStatus = $this->resolveStatusValue($bug->status);

            $submission = $this->createSubmission($bug->id, $user->id, $data->commitHash);

            $this->persistChanges($submission, $data->changes ?? []);

            $this->transitionToReadyToTestIfNeeded($bug, $originalStatus, $user);

            return $submission;
        });
    }

    private function createSubmission(int $bugId, int $userId, ?string $commitHash): BugSubmission
    {
        return BugSubmission::create([
            'bug_id' => $bugId,
            'user_id' => $userId,
            'commit_hash' => $commitHash,
        ]);
    }
    
    private function persistChanges(BugSubmission $submission, array $changes): void
    {
        if (empty($changes)) {
            return;
        }

        $payload = collect($changes)->map(fn ($c) => [
            'file' => $c['file'] ?? null,
            'diff' => $c['diff'] ?? null,
        ])->all();

        $submission->changes()->createMany($payload);
    }

    private function transitionToReadyToTestIfNeeded(Bug $bug, string $originalStatus, User $user): void
    {
        $target = BugStatuses::IN_REVIEW->value;

        if ($originalStatus === $target) {
            return; // avoid redundant updates / duplicate history
        }

        $bug->update(['status' => $target]);

        BugHistory::create([
            'bug_id' => $bug->id,
            'user_id' => $user->id,
            'type' => BugHistoryTypes::STATUS_CHANGE->value,
            'from_state' => $originalStatus,
            'to_state' => $target,
        ]);
    }

    private function resolveStatusValue(mixed $status): string
    {
        return $status instanceof BugStatuses ? $status->value : (string) $status;
    }
}
