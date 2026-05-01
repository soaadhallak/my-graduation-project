<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Actions\RegisterNewUserAction;
use App\Data\UserData;
use App\Http\Requests\RegisterUserRequest;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use App\Actions\LoginUserAction;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request, RegisterNewUserAction $action): JsonResponse
    {
        $data = $action->execute(UserData::from($request->validated()));
        $user = $data['user'];

        return UserResource::make($user->load(['media']))
            ->additional([
                'message' => ResponseMessages::CREATED->message(),
                'token' => $data['token']
            ])->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function login(LoginUserRequest $request, LoginUserAction $loginUserAction): UserResource
    {
        $result = $loginUserAction->execute(UserData::from($request->validated()));
        $user = $result['user'];
        $token = $result['token'];

        return UserResource::make($user->load(['media']))
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message(),
                'token' => $token
            ]);
    }

    public function logout(Request $request): UserResource
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        return UserResource::make($user->load(['media']))
            ->additional([
                'message' => ResponseMessages::DELETED->message(),
            ]);
    }
}
