<?php

namespace App\Data;

use App\Enums\BugPriorities;
use App\Enums\BugStatuses;
use App\Models\Bug;
use Illuminate\Http\UploadedFile;
use Mrmarchone\LaravelAutoCrud\Traits\HasModelAttributes;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;


class BugData extends Data
{
    use HasModelAttributes;

    protected static string $model = Bug::class;

    public function __construct(
        #[Max(255)]
        public ?string $title,
        #[Max(255)]
        public ?string $description,
        public ?string $status,
        public ?string $priority,
        public ?string $environment,
        #[Exists('projects', 'id')]
        public ?int $projectId,  
        #[Exists('users', 'id')]           
        public ?int $creatorId,
        #[Exists('users', 'id')]
        public ?int $assignedTo,
        public ?array $labels,
        public UploadedFile|Optional|null $screenshot,  
    ) {}
}
