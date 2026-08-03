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
use App\Http\Resources\GithubConfigResource;
use App\Http\Resources\ProjectMemberResource;
use App\Http\Resources\ProjectResource;
use Illuminate\Support\Facades\Auth;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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


    public function members(Project $project): AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        return ProjectMemberResource::collection($project->members()->get())
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message()
            ]);
    }


    public function githubConfig(Project $project): GithubConfigResource|JsonResponse
    {
        Gate::authorize('view', $project);

        $githubConfig = $project->githubConfig;

        if (!$githubConfig) {
            return response()->json([
                'message' => 'This project is not linked to a GitHub repository.'
            ], 404);
        }

        return GithubConfigResource::make($githubConfig)
            ->additional([
                'message' => ResponseMessages::RETRIEVED->message()
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

    /**
     * TEMPORARY — debug dump of all projects with github config + dependencies + job status.
     * Remove before production.
     */
    public function debugDump(): JsonResponse
    {
        $projects = Project::query()
            ->with(['githubConfig'])
            ->withCount('dependencies')
            ->latest()
            ->get()
            ->map(function (Project $project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'analysis_status' => $project->analysis_status,
                    'analysis_error' => $project->analysis_error,
                    'analysis_started_at' => $project->analysis_started_at,
                    'analysis_finished_at' => $project->analysis_finished_at,
                    'dependencies_count' => $project->dependencies_count,
                    'github_config' => $project->githubConfig,
                ];
            });

        $pendingJobs = DB::table('jobs')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                $displayName = $payload['displayName'] ?? null;

                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'attempts' => $job->attempts,
                    'display_name' => $displayName,
                    'available_at' => date('c', $job->available_at),
                    'created_at' => date('c', $job->created_at),
                    'reserved_at' => $job->reserved_at ? date('c', $job->reserved_at) : null,
                ];
            });

        $failedJobs = DB::table('failed_jobs')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                $exception = (string) $job->exception;
                $firstLine = strtok($exception, "\n") ?: $exception;

                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'queue' => $job->queue,
                    'display_name' => $payload['displayName'] ?? null,
                    'exception_summary' => mb_substr($firstLine, 0, 500),
                    'failed_at' => $job->failed_at,
                ];
            });

        return response()->json([
            'message' => 'TEMPORARY debug endpoint — remove before production',
            'queue_connection' => config('queue.default'),
            'projects' => $projects,
            'pending_jobs' => $pendingJobs,
            'failed_jobs' => $failedJobs,
        ]);
    }
}
