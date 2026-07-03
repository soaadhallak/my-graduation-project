<?php

namespace App\Data;

use App\Models\BugSubmission;
use Mrmarchone\LaravelAutoCrud\Traits\HasModelAttributes;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class BugSubmissionData extends Data
{
    use HasModelAttributes;
    protected static string $model = BugSubmission::class;

    public function __construct(
        #[Exists('bugs', 'id')]
        public ?int $bugId,
        #[Exists('users', 'id')]
        public ?int $userId,
        #[Max(255)]
        public ?string $commitHash,
        public ?array $changes,
        public ?string $reviewBranch,
    ) {}
}
