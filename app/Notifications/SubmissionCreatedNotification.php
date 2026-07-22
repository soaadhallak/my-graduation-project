<?php

namespace App\Notifications;

use App\Models\Bug;
use App\Models\User;

class SubmissionCreatedNotification extends BaseAppNotification
{
    public function __construct(
        protected Bug $bug,
        protected User $submitter
    ) {}

    public function type(): string
    {
        return 'submission_created';
    }

    public function title(): string
    {
        return 'Bug submitted for review';
    }

    public function message(): string
    {
        $this->bug->loadMissing('project');

        return "{$this->submitter->name} submitted a fix for \"{$this->bug->title}\" in {$this->bug->project->name}.";
    }

    protected function data(): array
    {
        return [
            'bug_id' => $this->bug->id,
            'project_id' => $this->bug->project_id,
            'submitter_id' => $this->submitter->id,
        ];
    }
}
