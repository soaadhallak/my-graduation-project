<?php

namespace App\Notifications;

use App\Models\BugSubmission;

class SubmissionRejectedNotification extends BaseAppNotification
{
    public function __construct(protected BugSubmission $submission) {}

    public function type(): string
    {
        return 'submission_rejected';
    }

    public function title(): string
    {
        return 'Submission rejected';
    }

    public function message(): string
    {
        $this->submission->loadMissing('bug');

        return "Your submission for \"{$this->submission->bug->title}\" was rejected.";
    }

    protected function data(): array
    {
        $this->submission->loadMissing('bug');

        return [
            'bug_id' => $this->submission->bug_id,
            'project_id' => $this->submission->bug->project_id,
            'submission_id' => $this->submission->id,
            'rejection_reason' => $this->submission->rejection_reason,
        ];
    }
}
