<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GithubConfigResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'projectId' => $this->project_id,
            'githubRepoId' => $this->github_repo_id,
            'fullName' => $this->full_name,
            'installationId' => $this->installation_id,
            'defaultBranch' => $this->default_branch,
            'project' => ProjectResource::make($this->whenLoaded('project')) 
        ];
    }
}
