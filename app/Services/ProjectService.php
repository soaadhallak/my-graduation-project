<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function store(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            return Project::create($data);
        });
    }

    public function update(Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            tap($project)->update($data);
            return $project;
        });
    }

    public function delete(Project $project): bool
    {
        return DB::transaction(function () use ($project) {
            return $project->delete();
        });
    }

    public function getAll()
    {
        return Project::latest()->get();
    }

    public function getById(Project $project)
    {
        return $project;
    }
}