<?php

namespace App\Data;

use App\Models\Dependencie;
use Mrmarchone\LaravelAutoCrud\Traits\HasModelAttributes;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class DependencieData extends Data
{
    use HasModelAttributes;
    protected static string $model = Dependencie::class;

    public function __construct(
        #[Exists('projects', 'id')]
        public ?int $projectId,
        #[Max(255)]
        public ?string $filePath,
        #[Max(255)]
        public ?string $dependsOn,
        public ?string $extension = null,
    ) {}
}
