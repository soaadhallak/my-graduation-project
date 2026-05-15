<?php

/*namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    //
}*/

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectService;
use App\Actions\Projects\CreateProjectAction;
use App\Actions\Projects\UpdateProjectAction;
use App\Actions\Projects\DeleteProjectAction;
use App\Http\Resources\ProjectResource;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $service) {}

    // 📌 عرض كل المشاريع
    public function index()
    {
        return ProjectResource::collection(
            $this->service->getAll()
        );
    }

    // 📌 إنشاء مشروع
    public function store(CreateProjectAction $action)
    {
        $project = $action->execute(request()->all());

        return new ProjectResource($project);
    }

    // 📌 عرض مشروع واحد
    public function show(Project $project)
    {
        return new ProjectResource(
            $this->service->getById($project)
        );
    }

    // 📌 تعديل مشروع
    public function update(Project $project, UpdateProjectAction $action)
    {
        $updated = $action->execute($project, request()->all());

        return new ProjectResource($updated);
    }

    // 📌 حذف مشروع
    public function destroy(Project $project, DeleteProjectAction $action)
    {
        $action->execute($project);

        return response()->json([
            'message' => 'Project deleted successfully'
        ]);
    }
}
