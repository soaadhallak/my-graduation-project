<?php

namespace App\Services;

use App\Enums\BugSubmissionStatus;
use App\Models\Bug;
use App\Models\BugSubmission;
use App\Models\User;
use App\Notifications\BugAssignedNotification;
use App\Notifications\BugCreatedNotification;
use App\Notifications\BugInProgressNotification;
use App\Notifications\BugQaFailedNotification;
use App\Notifications\BugQaPassedNotification;
use App\Notifications\SubmissionApprovedNotification;
use App\Notifications\SubmissionCreatedNotification;
use App\Notifications\SubmissionRejectedNotification;
use Illuminate\Support\Facades\DB;

class BugPushNotificationService
{
    public function notifyBugCreated(Bug $bug): void
    {
        $this->afterCommit(function () use ($bug) {
            $bug->loadMissing(['creator', 'project']);

            $this->notifyProjectManager(
                $bug,
                new BugCreatedNotification($bug),
                $bug->creator_id
            );
        });
    }

    public function notifyBugAssigned(Bug $bug, int $assigneeId): void
    {
        $this->afterCommit(function () use ($bug, $assigneeId) {
            $assignee = User::find($assigneeId);

            if (! $assignee) {
                return;
            }

            $assignee->notify(new BugAssignedNotification($bug));
        });
    }

    public function notifyBugInProgress(Bug $bug): void
    {
        $this->afterCommit(function () use ($bug) {
            $this->notifyProjectManager(
                $bug,
                new BugInProgressNotification($bug),
                $bug->assigned_to
            );
        });
    }

    public function notifySubmissionCreated(Bug $bug, User $submitter): void
    {
        $this->afterCommit(function () use ($bug, $submitter) {
            $this->notifyProjectManager(
                $bug,
                new SubmissionCreatedNotification($bug, $submitter),
                $submitter->id
            );
        });
    }

    public function notifySubmissionApproved(BugSubmission $submission): void
    {
        $this->afterCommit(function () use ($submission) {
            $submission->loadMissing(['user', 'bug.creator']);

            $submission->user?->notify(
                new SubmissionApprovedNotification($submission, 'submitter')
            );

            $bug = $submission->bug;

            if ($bug->creator_id !== $submission->user_id && $bug->creator) {
                $bug->creator->notify(
                    new SubmissionApprovedNotification($submission, 'creator')
                );
            }
        });
    }

    public function notifySubmissionRejected(BugSubmission $submission): void
    {
        $this->afterCommit(function () use ($submission) {
            $submission->loadMissing('user');

            $submission->user?->notify(new SubmissionRejectedNotification($submission));
        });
    }

    public function notifyQaPassed(Bug $bug): void
    {
        $this->afterCommit(function () use ($bug) {
            $submitterId = $this->notifyApprovedSubmitter(
                $bug,
                new BugQaPassedNotification($bug)
            );

            $this->notifyProjectManager(
                $bug,
                new BugQaPassedNotification($bug),
                $submitterId
            );
        });
    }

    public function notifyQaFailed(Bug $bug): void
    {
        $this->afterCommit(function () use ($bug) {
            $submitterId = $this->notifyApprovedSubmitter(
                $bug,
                new BugQaFailedNotification($bug)
            );

            $this->notifyProjectManager(
                $bug,
                new BugQaFailedNotification($bug),
                $submitterId
            );
        });
    }

    protected function notifyApprovedSubmitter(Bug $bug, object $notification): ?int
    {
        $submission = $bug->submissions()
            ->where('status', BugSubmissionStatus::Approved->value)
            ->latest()
            ->first();

        if (! $submission) {
            return null;
        }

        $submission->loadMissing('user');
        $submission->user?->notify($notification);

        return $submission->user_id;
    }

    protected function notifyProjectManager(
        Bug $bug,
        object $notification,
        ?int $exceptUserId = null
    ): void {
        $manager = $bug->project?->projectManager()
            ?? $bug->loadMissing('project')->project?->projectManager();

        if (! $manager || $manager->id === $exceptUserId) {
            return;
        }

        $manager->notify($notification);
    }

    protected function afterCommit(callable $callback): void
    {
        if (DB::transactionLevel() > 0) {
            DB::afterCommit($callback);

            return;
        }

        $callback();
    }
}
