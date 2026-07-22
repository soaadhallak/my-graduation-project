<?php

namespace App\Notifications;

use App\Models\Bug;

class BugQaFailedNotification extends BaseAppNotification
{
    public function __construct(protected Bug $bug) {}

    public function type(): string
    {
        return 'qa_failed';
    }

    public function title(): string
    {
        return 'QA failed';
    }

    public function message(): string
    {
        $this->bug->loadMissing('project');

        return "Bug \"{$this->bug->title}\" failed QA testing in {$this->bug->project->name}.";
    }

    protected function data(): array
    {
        return [
            'bug_id' => $this->bug->id,
            'project_id' => $this->bug->project_id,
        ];
    }
}
