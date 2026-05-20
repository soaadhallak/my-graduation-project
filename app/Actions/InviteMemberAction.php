<?php

namespace App\Actions;

use App\Data\InviteMemberData;
use App\Enums\InvitationStatus;
use App\Events\MemberInvited;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class InviteMemberAction
{

    public function execute(InviteMemberData $inviteMemberData, Project $project): Invitation
    {
        return DB::transaction(function () use ($inviteMemberData, $project) {

            $invitation = Invitation::create([
                'project_id' => $project->id,
                'token' => Str::random(32),
                'email' => $inviteMemberData->email,
                'status' => InvitationStatus::PENDING,
                'role' => $inviteMemberData->role,
                'expires_at' => now()->addDays(3)
            ]);

            $user = User::where('email', $inviteMemberData->email)->first();
            event(new MemberInvited($invitation, $project->name, $user->name));

            return $invitation;
        });
    }
}
