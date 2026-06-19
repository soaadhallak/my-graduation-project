<?php

namespace App\Policies;

use App\Models\Bug;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BugPolicy
{
    public function view(User $user, Bug $bug): bool
    {
        return $user->isMemberOfProject($bug->project_id);
    }

}
