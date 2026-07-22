<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;

class MemberJoinedProjectNotification extends BaseAppNotification
{
    public function __construct(
        protected User $member,
        protected Project $project,
        protected string $role
    ) {}

    public function type(): string
    {
        return 'member_joined';
    }

    public function title(): string
    {
        return 'New project member';
    }

    public function message(): string
    {
        return "{$this->member->name} joined your project ({$this->project->name}) as {$this->role}.";
    }

    protected function data(): array
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'member_id' => $this->member->id,
            'member_name' => $this->member->name,
            'member_role' => $this->role,
        ];
    }
}
