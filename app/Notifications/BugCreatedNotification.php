<?php

namespace App\Notifications;

use App\Models\Bug;

class BugCreatedNotification extends BaseAppNotification
{
    public function __construct(protected Bug $bug) {}

    public function type(): string
    {
        return 'bug_created';
    }

    public function title(): string
    {
        return 'New bug reported';
    }

    public function message(): string
    {
        $this->bug->loadMissing(['creator', 'project']);

        return "{$this->bug->creator->name} reported \"{$this->bug->title}\" in {$this->bug->project->name}.";
    }

    protected function data(): array
    {
        return [
            'bug_id' => $this->bug->id,
            'project_id' => $this->bug->project_id,
            'creator_id' => $this->bug->creator_id,
        ];
    }
}
