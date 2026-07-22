<?php

namespace App\Notifications;

use App\Models\Bug;

class BugAssignedNotification extends BaseAppNotification
{
    public function __construct(protected Bug $bug) {}

    public function type(): string
    {
        return 'bug_assigned';
    }

    public function title(): string
    {
        return 'Bug assigned to you';
    }

    public function message(): string
    {
        $this->bug->loadMissing('project');

        return "You were assigned to \"{$this->bug->title}\" in {$this->bug->project->name}.";
    }

    protected function data(): array
    {
        return [
            'bug_id' => $this->bug->id,
            'project_id' => $this->bug->project_id,
            'assigned_to' => $this->bug->assigned_to,
        ];
    }
}
