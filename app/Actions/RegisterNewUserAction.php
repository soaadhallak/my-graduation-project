<?php

namespace App\Actions;

use App\Data\AcceptInvitationData;
use App\Data\UserData;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Mrmarchone\LaravelAutoCrud\Helpers\MediaHelper;

class RegisterNewUserAction
{
    public function execute(UserData $data, AcceptInvitationAction $acceptInvitationAction): array
    {
        return DB::transaction(static function () use ($data, $acceptInvitationAction) {
            $user = User::create($data->onlyModelAttributes());

            if ($data->avatar instanceof UploadedFile) {
                MediaHelper::uploadMedia($data->avatar, $user, 'primary-image');
            }

            if ($data->token) {
                $acceptInvitationAction->execute($user, AcceptInvitationData::from(['token' => $data->token]));
            }

            $token = $user->createToken('user-token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token
            ];
        });
    }
}
