<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class AcceptInvitationData extends Data
{
    public function __construct(
        public ?string $token
    ) {}
}
