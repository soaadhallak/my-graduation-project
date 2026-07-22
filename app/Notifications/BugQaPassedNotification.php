<?php

namespace App\Notifications;

use App\Models\Bug;

class BugQaPassedNotification extends BaseAppNotification
{
    public function __construct(protected Bug $bug) {}

    public function type(): string
    {
        return 'qa_passed';
    }

    public function title(): string
    {
        return 'Bug closed';
    }

    public function message(): string
    {
        $this->bug->loadMissing('project');

        return "Bug \"{$this->bug->title}\" passed QA and was closed in {$this->bug->project->name}.";
    }

    protected function data(): array
    {
        return [
            'bug_id' => $this->bug->id,
            'project_id' => $this->bug->project_id,
        ];
    }
}
