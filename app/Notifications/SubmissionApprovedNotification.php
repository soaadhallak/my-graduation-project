<?php

namespace App\Notifications;

use App\Models\BugSubmission;

class SubmissionApprovedNotification extends BaseAppNotification
{
    public function __construct(
        protected BugSubmission $submission,
        protected string $audience = 'submitter'
    ) {}

    public function type(): string
    {
        return 'submission_approved';
    }

    public function title(): string
    {
        return 'Submission approved';
    }

    public function message(): string
    {
        $this->submission->loadMissing('bug');
        $bugTitle = $this->submission->bug->title;

        if ($this->audience === 'creator') {
            return "A fix for your bug \"{$bugTitle}\" was approved and is ready for QA.";
        }

        return "Your submission for \"{$bugTitle}\" was approved.";
    }

    protected function data(): array
    {
        $this->submission->loadMissing('bug');

        return [
            'bug_id' => $this->submission->bug_id,
            'project_id' => $this->submission->bug->project_id,
            'submission_id' => $this->submission->id,
            'audience' => $this->audience,
        ];
    }
}
