<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dependencie;
use App\Models\Project;
use App\Services\Dependency\DependencyMapBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Mrmarchone\LaravelAutoCrud\Enums\ResponseMessages;

class DependencyController extends Controller
{
    public function __construct(
        protected DependencyMapBuilder $dependencyMapBuilder
    ) {}

    public function index(Project $project, Request $request): JsonResponse
    {
        Gate::authorize('view', $project);

        $dependencies = Dependencie::query()
            ->where('project_id', $project->id)
            ->when($request->filled('file'), function ($query) use ($request) {
                $query->where('file_path', str_replace('\\', '/', $request->string('file')->toString()));
            })
            ->when($request->boolean('resolved_only'), function ($query) {
                $query->whereNotNull('depends_on_path');
            })
            ->orderBy('file_path')
            ->paginate($request->input('perPage', 50));

        return response()->json([
            'message' => ResponseMessages::RETRIEVED->message(),
            'data' => $dependencies->items(),
            'meta' => [
                'current_page' => $dependencies->currentPage(),
                'last_page' => $dependencies->lastPage(),
                'per_page' => $dependencies->perPage(),
                'total' => $dependencies->total(),
            ],
        ]);
    }

    /**
     * Export reverse dependency map for the BugTrak CLI tool.
     */
    public function map(Project $project): JsonResponse
    {
        Gate::authorize('view', $project);

        return response()->json(
            $this->dependencyMapBuilder->build($project->id)
        );
    }
}
