<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BugResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'environment' => $this->environment,
            'project' => ProjectResource::make($this->whenLoaded('project')),
            'creator' => UserResource::make($this->whenLoaded('creator')),
            'assignedTo' => UserResource::make($this->whenLoaded('assignedUser')),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'screenshot' => $this->whenLoaded('media', function () {
                $media = $this->getMedia('screenshot')->first();

                return $media ? MediaResource::make($media): null;
            }),
            'createdAt' => $this->created_at?->diffForHumans(),
        ];
    }
}
