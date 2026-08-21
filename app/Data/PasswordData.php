<?php

namespace App\Data;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
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
