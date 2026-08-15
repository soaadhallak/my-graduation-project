<?php

namespace App\Services;

use App\Enums\BugStatuses;
use App\Models\Bug;
use App\Models\User;

class DashboardStatsService
{
    public function getStats(User $user): array
    {
        $projectIds = $user->projects()->pluck('projects.id');

        return [
            'openBugs' => $this->bugsInProjects($projectIds)
                ->where('status', BugStatuses::OPEN)
                ->count(),
            'inProgress' => $this->bugsInProjects($projectIds)
                ->where('status', BugStatuses::IN_PROGRESS)
                ->count(),
            'resolved' => $this->bugsInProjects($projectIds)
                ->where('status', BugStatuses::CLOSED)
                ->count(),
            'activeProjects' => $projectIds->count(),
            'myAssigned' => $user->assignedBugs()
                ->where('status', '!=', BugStatuses::CLOSED)
                ->count(),
        ];
    }

    private function bugsInProjects($projectIds)
    {
        return Bug::query()->whereIn('project_id', $projectIds);
    }
}
