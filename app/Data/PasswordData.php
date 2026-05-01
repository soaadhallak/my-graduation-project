<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;

class PasswordData extends Data
{
    public function __construct(
        #[Exists('users','email')]
        public ?string $email,
        public ?string $token,
        public ?string $password,
        public ?string $passwordConfirmation,
    ) {}
}
