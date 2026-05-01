<?php

namespace App\Data;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Mrmarchone\LaravelAutoCrud\Traits\HasModelAttributes;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UserData extends Data
{
    use HasModelAttributes;
    protected static string $model = User::class;

    public function __construct(
        #[Max(255)]
        public ?string $name,
        #[Max(255), Unique('users', 'email'), Email()]
        public ?string $email,
        #[Max(255), Min(8), Confirmed]
        public ?string $password,
        public UploadedFile|Optional|null $avatar,
    ) {}
}
