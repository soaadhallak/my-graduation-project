<?php

namespace App\Http\Resources;

use App\Enums\BugHistoryTypes;
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
            'from' => $this->resolveFrom(),
            'to' => $this->resolveTo(),
            'notes' => $this->notes,
            'user' => UserResource::make($this->whenLoaded('user')),
            'bug' => BugResource::make($this->whenLoaded('bug')),
            'createdAt' => $this->created_at?->toDateTimeString(),
        ];
    }

    private function resolveFrom(): ?string
    {
        if ($this->type === BugHistoryTypes::ASSIGNMENT_CHANGE) {
            return $this->relationLoaded('fromAssignee')
                ? $this->getRelation('fromAssignee')?->name
                : null;
        }

        return $this->from_state;
    }

    private function resolveTo(): ?string
    {
        if ($this->type === BugHistoryTypes::ASSIGNMENT_CHANGE) {
            return $this->relationLoaded('toAssignee')
                ? $this->getRelation('toAssignee')?->name
                : null;
        }

        return $this->to_state;
    }
}
