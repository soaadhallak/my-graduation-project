<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BugSubmissionResource extends JsonResource
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
            'bug' => BugResource::make($this->whenLoaded('bug')),
            'submittedBy' => UserResource::make($this->whenLoaded('user')),
            'commitHash' => $this->commit_hash,
            'submittedAt' => $this->created_at->toDateTimeString(),
            'changes' => BugSubmissionChangeResource::collection($this->whenLoaded('changes')),
            'pullRequestNumber' => $this->pull_request_number
        ];
    }
}
