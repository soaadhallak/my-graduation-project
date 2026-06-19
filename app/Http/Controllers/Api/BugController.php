<?php

namespace App\Http\Controllers\Api;

use App\Data\BugData;
use App\Http\Controllers\Controller;
use App\Http\Requests\BugFilterRequest;
use App\Http\Requests\StoreBugRequest;
use App\Http\Requests\UpdateBugRequest;
use App\Http\Resources\BugResource;
use App\Models\Bug;
use App\Models\Project;
use App\Services\BugService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class BugController extends Controller
{

    public function __construct(
        protected BugService $bugService
    )
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Project $project, BugFilterRequest $request): AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        $bugs = Bug::getQuery()
            ->where('project_id', $project->id)
            ->with(['creator', 'assignedUser', 'labels'])
            ->paginate($request->input('perPage', 15))
            ->withQueryString();

        return BugResource::collection($bugs)
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message()
            ]);    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBugRequest $request): JsonResponse
    {
        $bug = $this->bugService->store(BugData::from($request->validated()), Auth::user());

        return BugResource::make($bug->load(['project', 'creator', 'assignedUser', 'labels', 'media']))
            ->additional([
                'message' => ResponseMessages::CREATED->message()
            ])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bug $bug): BugResource
    {
        Gate::authorize('view', $bug);

        return BugResource::make($bug->load([
            'creator',
            'assignedUser',
            'labels',
            'histories.user'])
        )->additional([
            'message' => ResponseMessages::RETRIEVED->message()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBugRequest $request, Bug $bug): BugResource
    {
        Log::info('User ID ' . Auth::id() . ' is updating bug ID ' . $bug->id);
        Log::info('Request data: ' . json_encode($request->all()));
        $bug = $this->bugService->update($bug, BugData::from($request->validated()), Auth::user());

        return BugResource::make($bug->load(['project', 'creator', 'assignedUser', 'labels', 'media']))
            ->additional([
                'message' => ResponseMessages::UPDATED->message()
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
