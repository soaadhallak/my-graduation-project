<?php

namespace App\Http\Controllers\API;

use App\Actions\InviteMemberAction;
use App\Data\InviteMemberData;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteMemberRequest;
use App\Http\Resources\InvitationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;

class InviteMembersController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(InviteMemberRequest $request, InviteMemberAction $inviteMemberAction)
    {
        $invitations = $inviteMemberAction->execute(InviteMemberData::from($request->validated()));

        return InvitationResource::make($invitations->load(['project', 'user']))
            ->additional([
                'message'=>ResponseMessages::CREATED->message()
            ])->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
