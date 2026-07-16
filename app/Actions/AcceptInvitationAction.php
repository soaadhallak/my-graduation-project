<?php

namespace App\Actions;

use App\Data\AcceptInvitationData;
use App\Enums\InvitationStatus;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\ProjectUser;
use App\Models\User;
use App\Notifications\MemberJoinedProjectNotification;
use Illuminate\Support\Facades\DB;

class AcceptInvitationAction
{
    public function execute(?User $user, AcceptInvitationData $acceptInvitationData)
    {
        $invitation = Invitation::where('token', $acceptInvitationData->token)
            ->firstOrFail();
            
        if ($user) {
            if ($invitation->email != $user->email) {
                throw new \Exception(__('This invitation is for another email'));
            }

            $this->completeJoinProccess($invitation, $user);

            return [
                'status' => 'success',
                'invitation' => $invitation,
            ];
        }
        
        $existingUser = User::where('email', $invitation->email)->first();

        if ($existingUser) {
            return [
                'status'  => 'login_required',
                'invitation' => $invitation
            ];
        }

        return [
            'status'  => 'register_required',
            'invitation' => $invitation
        ];
    }

    private function completeJoinProccess(Invitation $invitation, User $user)
    {
        $projectManagerForProject = ProjectUser::where('project_id', $invitation->project_id)
            ->where('role', 'project_manager')
            ->first();

        $project = Project::find($invitation->project_id);

        return DB::transaction(function () use ($user, $invitation, $projectManagerForProject, $project) {

            $invitation->update([
                'status' => InvitationStatus::ACCEPTED,
            ]);

            setPermissionsTeamId($invitation->project_id);

            $user->assignRole($invitation->role);

            ProjectUser::create([
                'project_id' => $invitation->project_id,
                'user_id'    => $user->id,
                'role'       => $invitation->role,
            ]);


            if ($projectManagerForProject && $project) {
                $projectManager = User::find($projectManagerForProject->user_id);
                $projectManager->notify(new MemberJoinedProjectNotification($user, $project, $invitation->role));
            }
        });
    }
}
