<?php

namespace App\Data;

use App\Models\GithubConfig;
use Mrmarchone\LaravelAutoCrud\Traits\HasModelAttributes;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;

class GithubConfigData extends Data
{
    use HasModelAttributes;
    protected static string $model = GithubConfig::class;

    public function __construct(
        #[Exists(table: 'projects', column: 'id')]
        public ?int $projectId,
        #[Unique(table: 'github_configs', column: 'github_repo_id')]
        public ?string $githubRepoId,
        #[Max(255)]
        public ?string $fullName,
        #[Max(255)]
        public ?string $installationId,
        public ?string $defaultBranch = null,
    ) {}
}
