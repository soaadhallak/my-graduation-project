<?php

namespace App\Notifications;

use App\Models\Bug;

class BugInProgressNotification extends BaseAppNotification
{
    public function __construct(protected Bug $bug) {}

    public function type(): string
    {
        return 'bug_in_progress';
    }

    public function title(): string
    {
        return 'Bug in progress';
    }

    public function message(): string
    {
        $this->bug->loadMissing(['assignedUser', 'project']);
        $workerName = $this->bug->assignedUser?->name ?? 'A teammate';

        return "{$workerName} started working on \"{$this->bug->title}\" in {$this->bug->project->name}.";
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
