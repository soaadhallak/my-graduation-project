<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectService;
use App\Actions\Projects\CreateProjectAction;
use App\Actions\Projects\UpdateProjectAction;
use App\Actions\Projects\DeleteProjectAction;
use App\Data\ProjectData;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use Illuminate\Support\Facades\Auth;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $projectService) {}


    public function index(): AnonymousResourceCollection
    {
        $projects = Project::whereHas('members', function ($query) {
            $query->where('user_id', Auth::id());
        })->latest()->get();

        return ProjectResource::collection($projects->load(['members']))
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message()
            ]);          
    }


    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->store(ProjectData::from($request->validated()), Auth::user());

        return ProjectResource::make($project->load(['members']))
            ->additional([
                'message' => ResponseMessages::CREATED->message()
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project): ProjectResource
    {
        Gate::authorize('view', $project);

        return ProjectResource::make($project->load(['members']))
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message()
            ]);
    }


    public function update(Project $project, UpdateProjectRequest $request): ProjectResource
    {
        Gate::authorize('update', $project);

        $project = $this->projectService->update(ProjectData::from($request->validated()), $project);

        return ProjectResource::make($project->load(['members']))
            ->additional([
                'message' => ResponseMessages::UPDATED->message()
            ]);
    }


    public function destroy(Project $project): ProjectResource
    {
        Gate::authorize('delete', $project);
        
        $project->delete();

        return ProjectResource::make($project)
            ->additional([
                'message' => ResponseMessages::DELETED->message()
            ]);
    }
}
