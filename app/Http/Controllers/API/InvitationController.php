<?php

namespace App\Http\Controllers\API;

use App\Actions\AcceptInvitationAction;
use App\Actions\InviteMemberAction;
use App\Data\AcceptInvitationData;
use App\Data\InviteMemberData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcceptInvitationRequest;
use App\Http\Requests\InviteMemberRequest;
use App\Http\Resources\InvitationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;

class InvitationController extends Controller
{
    public function inviteMember(InviteMemberRequest $request, InviteMemberAction $inviteMemberAction) {
        $invitations = $inviteMemberAction->execute(InviteMemberData::from($request->validated()));

        return InvitationResource::make($invitations->load(['project', 'user']))
            ->additional([
                'message'=>ResponseMessages::CREATED->message()
            ])->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function acceptInvitation(AcceptInvitationRequest $request, AcceptInvitationAction $acceptInvitationAction) {
        $invitation=$acceptInvitationAction->execute(Auth::user(), AcceptInvitationData::from($request->validated()));

        return InvitationResource::make($invitation->load(['user']))
            ->additional([
                'message'=>ResponseMessages::RETRIEVED->message()
            ]);
    }
}
