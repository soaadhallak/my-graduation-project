<?php

namespace App\Data;

use App\Models\Project;
use Mrmarchone\LaravelAutoCrud\Traits\HasModelAttributes;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class ProjectData extends Data
{
    use HasModelAttributes;
    protected static string $model = Project::class;

    public function __construct(
        #[Max(255)]
        public ?string $name,
        #[Max(255)]
        public ?string $description,
    ) {}
}
