<?php

namespace App\Services;

use App\Actions\SendFirebaseNotificationAction;
use App\Enums\BugSubmissionStatus;
use App\Models\Bug;
use App\Models\BugSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BugPushNotificationService
{
    public function __construct(
        protected SendFirebaseNotificationAction $sendFirebaseNotificationAction
    ) {}

    public function notifyBugCreated(Bug $bug): void
    {
        $this->afterCommit(function () use ($bug) {
            $bug->loadMissing(['creator', 'project']);

            $this->notifyProjectManager(
                $bug,
                'bug_created',
                'New bug reported',
                "{$bug->creator->name} reported \"{$bug->title}\" in {$bug->project->name}.",
                $bug->creator_id
            );
        });
    }

    public function notifyBugAssigned(Bug $bug, int $assigneeId): void
    {
        $this->afterCommit(function () use ($bug, $assigneeId) {
            $bug->loadMissing('project');
            $assignee = User::find($assigneeId);

            if (! $assignee) {
                return;
            }

            $this->notifyUser(
                $assignee,
                'bug_assigned',
                'Bug assigned to you',
                "You were assigned to \"{$bug->title}\" in {$bug->project->name}."
            );
        });
    }

    public function notifyBugInProgress(Bug $bug): void
    {
        $this->afterCommit(function () use ($bug) {
            $bug->loadMissing(['assignedUser', 'project']);
            $workerName = $bug->assignedUser?->name ?? 'A teammate';

            $this->notifyProjectManager(
                $bug,
                'bug_in_progress',
                'Bug in progress',
                "{$workerName} started working on \"{$bug->title}\" in {$bug->project->name}.",
                $bug->assigned_to
            );
        });
    }

    public function notifySubmissionCreated(Bug $bug, User $submitter): void
    {
        $this->afterCommit(function () use ($bug, $submitter) {
            $bug->loadMissing('project');

            $this->notifyProjectManager(
                $bug,
                'submission_created',
                'Bug submitted for review',
                "{$submitter->name} submitted a fix for \"{$bug->title}\" in {$bug->project->name}.",
                $submitter->id
            );
        });
    }

    public function notifySubmissionApproved(BugSubmission $submission): void
    {
        $this->afterCommit(function () use ($submission) {
            $submission->loadMissing(['user', 'bug.creator', 'bug.project']);
            $bug = $submission->bug;

            $this->notifyUser(
                $submission->user,
                'submission_approved',
                'Submission approved',
                "Your submission for \"{$bug->title}\" was approved."
            );

            if ($bug->creator_id !== $submission->user_id) {
                $this->notifyUser(
                    $bug->creator,
                    'submission_approved',
                    'Submission approved',
                    "A fix for your bug \"{$bug->title}\" was approved and is ready for QA."
                );
            }
        });
    }

    public function notifySubmissionRejected(BugSubmission $submission): void
    {
        $this->afterCommit(function () use ($submission) {
            $submission->loadMissing(['user', 'bug']);

            $this->notifyUser(
                $submission->user,
                'submission_rejected',
                'Submission rejected',
                "Your submission for \"{$submission->bug->title}\" was rejected."
            );
        });
    }

    public function notifyQaPassed(Bug $bug): void
    {
        $this->afterCommit(function () use ($bug) {
            $bug->loadMissing('project');
            $body = "Bug \"{$bug->title}\" passed QA and was closed in {$bug->project->name}.";
            $submitterId = $this->notifyApprovedSubmitter($bug, 'qa_passed', 'Bug closed', $body);

            $this->notifyProjectManager($bug, 'qa_passed', 'Bug closed', $body, $submitterId);
        });
    }

    public function notifyQaFailed(Bug $bug): void
    {
        $this->afterCommit(function () use ($bug) {
            $bug->loadMissing('project');
            $body = "Bug \"{$bug->title}\" failed QA testing in {$bug->project->name}.";
            $submitterId = $this->notifyApprovedSubmitter($bug, 'qa_failed', 'QA failed', $body);

            $this->notifyProjectManager($bug, 'qa_failed', 'QA failed', $body, $submitterId);
        });
    }

    protected function notifyApprovedSubmitter(Bug $bug, string $type, string $title, string $body): ?int
    {
        $submission = $bug->submissions()
            ->where('status', BugSubmissionStatus::Approved->value)
            ->latest()
            ->first();

        if (! $submission) {
            return null;
        }

        $submission->loadMissing('user');

        $this->notifyUser($submission->user, $type, $title, $body);

        return $submission->user_id;
    }

    protected function notifyProjectManager(
        Bug $bug,
        string $type,
        string $title,
        string $body,
        ?int $exceptUserId = null
    ): void {
        $manager = $bug->project?->projectManager()
            ?? $bug->loadMissing('project')->project?->projectManager();

        if (! $manager || $manager->id === $exceptUserId) {
            return;
        }

        $this->notifyUser($manager, $type, $title, $body);
    }

    protected function notifyUser(?User $user, string $type, string $title, string $body): void
    {
        if (! $user) {
            return;
        }

        try {
            $this->sendFirebaseNotificationAction->execute($user, $title, $body, $type);
        } catch (\Throwable $e) {
            Log::error('Firebase push failed', [
                'type' => $type,
                'title' => $title,
                'to' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'error' => $e->getMessage(),
            ]);
        }
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
