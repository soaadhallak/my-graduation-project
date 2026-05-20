<?php

namespace App\Data;

use App\Models\Invitation;
use Mrmarchone\LaravelAutoCrud\Traits\HasModelAttributes;
use Spatie\LaravelData\Data;

class InviteMemberData extends Data
{
     use HasModelAttributes;
    protected static string $model = Invitation::class;

    public function __construct(
        public string $email,
        public string $role,
        public int $projectId
    ) {}
}
