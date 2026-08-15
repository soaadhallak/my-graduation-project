<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'openBugs' => $this->resource['openBugs'],
            'inProgress' => $this->resource['inProgress'],
            'resolved' => $this->resource['resolved'],
            'activeProjects' => $this->resource['activeProjects'],
            'myAssigned' => $this->resource['myAssigned'],
        ];
    }
}
