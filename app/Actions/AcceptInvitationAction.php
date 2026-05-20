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


    public function execute(User $user, AcceptInvitationData $acceptInvitationData)
    {
        $invitation = Invitation::where('token', $acceptInvitationData->token)
                ->firstOrFail();

        $projectManagerForProject = ProjectUser::where('project_id', $invitation->project_id)
            ->where('role', 'project_manager')
            ->with('user')
            ->first();

        $project = Project::find($invitation->project_id);

        return DB::transaction(function() use ($user, $invitation, $projectManagerForProject, $project){
            
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
                $projectManagerForProject->notify(new MemberJoinedProjectNotification($user, $project, $invitation->role));
            }

            return $invitation;
        });
    }
}
