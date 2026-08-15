<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Bug;
use App\Models\User;

class BugPolicy
{
    public function view(User $user, Bug $bug): bool
    {
        return $user->isMemberOfProject($bug->project_id);
    }

    public function delete(User $user, Bug $bug): bool
    {
        if ($user->id == $bug->creator_id) {
            return true;
        }

        return $user->isMemberOfProject($bug->project_id, UserRole::PROJECT_MANAGER->value);
    }
}
