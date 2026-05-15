<?php

namespace App\Actions\Projects;

use App\Services\ProjectService;

class CreateProjectAction
{
    public function __construct(private ProjectService $service) {}

    public function execute(array $data)
    {
        return $this->service->store($data);
    }
}