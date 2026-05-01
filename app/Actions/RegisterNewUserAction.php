<?php

namespace App\Actions;

use App\Data\UserData;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Mrmarchone\LaravelAutoCrud\Helpers\MediaHelper;

class RegisterNewUserAction
{
    public function execute(UserData $data): array
    {
        return DB::transaction(static function () use ($data) {
            $user = User::create($data->onlyModelAttributes());

            if ($data->avatar instanceof UploadedFile) {
                MediaHelper::uploadMedia($data->avatar, $user, 'primary-image');
            }

            $token = $user->createToken('user-token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token
            ];
        });
    }
}
