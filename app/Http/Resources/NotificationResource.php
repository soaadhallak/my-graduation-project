<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'type' => $this->data['type'] ?? class_basename($this->type),
            'title' => $this->data['title'] ?? null,
            'message' => $this->data['message'] ?? null,
            'data' => $this->data,
            'isRead' => $this->read_at !== null,
            'readAt' => $this->read_at?->toDateTimeString(),
            'createdAt' => $this->created_at?->toDateTimeString(),
        ];
    }
}
