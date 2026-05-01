<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequests\UserStoreRequest;
use App\Http\Requests\UserRequests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    /**
     * User API Controller
     *
     * Handles CRUD operations for Users.
     */
class UserController extends Controller
{
    /**
     * Get a paginated list of users.
     *
     * @return {{ AnonymousResourceCollection<resource> }}
     */
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return UserResource::collection(User::latest()->paginate(10));
    }

    /**
     * Create a new user.
     *
     * @param UserStoreRequest $request
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function store(UserStoreRequest $request): UserResource|\Illuminate\Http\JsonResponse
    {
        try {
            $user = User::create($request->validated());
            return new UserResource($user);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a specific user by ID.
     *
     * @param User $user
     * @return UserResource
     */
    public function show(User $user): UserResource
    {
        $user;
        return UserResource::make($user);
    }

    /**
     * Update an existing user.
     *
     * @param UserUpdateRequest $request
     * @param User $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     */
    public function update(UserUpdateRequest $request, User $user): UserResource|\Illuminate\Http\JsonResponse
    {
        try {
            $user->update($request->validated());
            return new UserResource($user);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a user.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user): \Illuminate\Http\JsonResponse
    {
        try {
            $user->delete();
            return response()->json(['message' => 'Deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            report($exception);
            return response()->json(['error' => 'There is an error.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
