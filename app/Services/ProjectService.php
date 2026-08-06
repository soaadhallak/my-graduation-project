<?php

namespace App\Services;

use App\Data\ProjectData;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function store(ProjectData $data, User $user): Project
    {
        return DB::transaction(function () use ($data, $user) {
            $project = Project::create($data->onlyModelAttributes());

            ProjectUser::create([
                'project_id' => $project->id,
                'user_id'    => $user->id,
                'role'       => 'project_manager',
            ]);

            setPermissionsTeamId($project->id);     
            $user->assignRole('project_manager');

            return $project;
        });
    }

    public function update(ProjectData $data,  Project $project): Project
    {
        return DB::transaction(function () use ($data, $project) {
            tap($project)->update($data->onlyModelAttributes());

            return $project;
        });
    }

    public function updateMemberRole(ProjectUser $projectUser, string $role): ProjectUser
    {
        return DB::transaction(function () use ($projectUser, $role) {
            $projectUser->update(['role' => $role]);

            $user = $projectUser->user;
            setPermissionsTeamId($projectUser->project_id);
            $user->syncRoles([$role]);

            return $projectUser->load('user');
        });
    }

    public function removeMember(ProjectUser $projectUser): ProjectUser
    {
        return DB::transaction(function () use ($projectUser) {
            $projectUser->loadMissing('user');
            $user = $projectUser->user;

            $projectUser->delete();

            setPermissionsTeamId($projectUser->project_id);
            $user->syncRoles([]);

            return $projectUser;
        });
    }
}
