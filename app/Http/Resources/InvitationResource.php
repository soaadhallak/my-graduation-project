<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvitationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id'=>$this->id,
            'project'=>ProjectResource::make($this->whenLoaded('project')),
            'email'=> $this->email,
            'status'=>$this->status,
            'user'=>UserResource::make($this->whenLoaded('user')),
            'expiresAt'=>$this->expires_at?->format('Y-m-d H:i'),
            'role'=>$this->role,
            'createdAt'=>$this->created_at?->diffForHumans(),
        ];
    }
}
