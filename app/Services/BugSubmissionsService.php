<?php

namespace App\Services;

use App\Data\BugSubmissionData;
use App\Enums\BugHistoryTypes;
use App\Enums\BugStatuses;
use App\Enums\BugSubmissionStatus;
use App\Models\Bug;
use App\Models\BugHistory;
use App\Models\BugSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BugSubmissionsService
{
    public function __construct(protected SocialAuthService $githubService) {}

    public function store(BugSubmissionData $data, User $user): BugSubmission
    {
        $bug = Bug::findOrFail($data->bugId);
        $githubConfig = $bug->project->githubConfig;

        return DB::transaction(function () use ($bug, $data, $user, $githubConfig) {
            $originalStatus = $this->resolveStatusValue($bug->status);
            $repoFullName = $githubConfig->full_name;
            $owner = Str::before($repoFullName, '/');
            $repo = Str::after($repoFullName, '/');

            $pullRequestNumber = $this->githubService->createPullRequest(
                $githubConfig->installation_id,
                $owner,
                $repo,
                $data->reviewBranch,
                $githubConfig->default_branch,
                "Fix Bug #{$bug->id}: " . $bug->title
            );

            $submission = $this->createSubmission($bug->id, $user->id, $data->commitHash, $pullRequestNumber, $data->reviewBranch);

            $this->persistChanges($submission, $data->changes ?? []);

            $this->transitionBug($bug, $originalStatus, $user, BugStatuses::IN_REVIEW->value);

            return $submission;
        });
    }

    private function createSubmission(int $bugId, int $userId, ?string $commitHash, int $pullRequestNumber, string $reviewBranch): BugSubmission
    {
        return BugSubmission::create([
            'bug_id' => $bugId,
            'user_id' => $userId,
            'commit_hash' => $commitHash,
            'pull_request_number' => $pullRequestNumber,
            'review_branch' => $reviewBranch
        ]);
    }

    private function persistChanges(BugSubmission $submission, array $changes): void
    {
        if (empty($changes)) {
            return;
        }

        $payload = collect($changes)->map(fn($c) => [
            'file' => $c['file'] ?? null,
            'diff' => $c['diff'] ?? null,
        ])->all();

        $submission->changes()->createMany($payload);
    }

    private function transitionBug(Bug $bug, string $originalStatus, User $user, string $target, string $notes = ''): void
    {
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
            'notes' => $notes
        ]);
    }

    private function resolveStatusValue(mixed $status): string
    {
        return $status instanceof BugStatuses ? $status->value : (string) $status;
    }

    public function approve(BugSubmission $submission, User $manager): BugSubmission
    {
        $bug = $submission->bug;
        $githubConfig = $bug->project->githubConfig;

        return DB::transaction(function () use ($submission, $bug, $manager, $githubConfig) {

            $repoFullName = $githubConfig->full_name;
            $owner = Str::before($repoFullName, '/');
            $repo = Str::after($repoFullName, '/');
            $originalStatus = $this->resolveStatusValue($bug->status);

            $this->githubService->mergePullRequest(
                $githubConfig->installation_id,
                $owner,
                $repo,
                $submission->pull_request_number,
                "Squash merge: Fix Bug #{$bug->id}"
            );


            $this->githubService->deleteBranch(
                $githubConfig->installation_id,
                $owner,
                $repo,
                $submission->review_branch
            );

            /* $submission->update([
                'status' => 'approved', // أو Enum الخاص بالتسليمات إن وجد
                'approved_by' => $manager->id, // إذا كان لديك هذا الحقل
                'approved_at' => now(),
            ]);*/

            $submission->update([
                'status' => BugSubmissionStatus::Approved->value
            ]);

            $this->transitionBug($bug, $originalStatus, $manager, BugStatuses::READY_FOR_QA->value);

            return $submission;
        });
    }

    public function reject(BugSubmission $submission, string $reason, User $manager): BugSubmission
    {
        $bug = $submission->bug;
        $githubConfig = $bug->project->githubConfig;

        return DB::transaction(function () use ($bug, $submission, $reason, $manager, $githubConfig) {
            $repoFullName = $githubConfig->full_name;
            $owner = Str::before($repoFullName, '/');
            $repo = Str::after($repoFullName, '/');

            $this->githubService->closePullRequest(
                $githubConfig->installation_id,
                $owner,
                $repo,
                $submission->pull_request_number
            );

            $this->githubService->deleteBranch(
                $githubConfig->installation_id,
                $owner,
                $repo,
                $submission->review_branch
            );

            $submission->update([
                'status' => BugSubmissionStatus::REJECTED->value, 
                'rejection_reason' => $reason
            ]);

            $originalStatus = $bug->status instanceof BugStatuses ? $bug->status->value : (string) $bug->status;


            $this->transitionBug($bug, $originalStatus, $manager, BugStatuses::CHANGES_REQUESTED->value, $reason);

            return $submission;
        });
    }
}
