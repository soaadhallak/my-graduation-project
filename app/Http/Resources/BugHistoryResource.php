<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BugHistoryResource extends JsonResource
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
            'type' => $this->type,
            'fromState' => $this->from_state,
            'toState' => $this->to_state,
            'notes' => $this->notes,
            'user' => UserResource::make($this->whenLoaded('user')),
            'bug' => BugResource::make($this->whenLoaded('bug')),
            'createdAt' => $this->created_at?->toDateTimeString(),
        ];
    }
}
