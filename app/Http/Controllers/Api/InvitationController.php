<?php

namespace App\Http\Controllers\Api;

use App\Actions\AcceptInvitationAction;
use App\Actions\InviteMemberAction;
use App\Data\AcceptInvitationData;
use App\Data\InviteMemberData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptInvitationRequest;
use App\Http\Requests\InviteMemberRequest;
use App\Http\Resources\InvitationResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;

class InvitationController extends Controller
{
    public function inviteMember(InviteMemberRequest $request, InviteMemberAction $inviteMemberAction, Project $project)
    {
        $invitations = $inviteMemberAction->execute(InviteMemberData::from($request->validated()), $project);

        return InvitationResource::make($invitations->load(['project']))
            ->additional([
                'message' => ResponseMessages::CREATED->message()
            ])->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function acceptInvitation(AcceptInvitationRequest $request, AcceptInvitationAction $acceptInvitationAction)
    {
        $result = $acceptInvitationAction->execute(Auth::user(), AcceptInvitationData::from($request->validated()));

        if ($result['status'] == 'login_required') {
            return InvitationResource::make($result['invitation']->load(['user']))
                ->additional([
                    'message' => ResponseMessages::RETRIEVED->message(),
                    'status' => 'login_required'
                ]);
        }

        if ($result['status'] == 'register_required') {
            return InvitationResource::make($result['invitation']->load(['user']))
                ->additional([
                    'message' => ResponseMessages::RETRIEVED->message(),
                    'status' => 'register_required'
                ]);
        }
        return InvitationResource::make($result['invitation']->load(['user']))
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message(),
                'status' => 'success'
            ]);
    }
}
