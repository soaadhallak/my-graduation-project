<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BugSubmission;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BugSubmissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BugSubmission $bugSubmission): bool
    {
        return $user->isMemberOfProject($bugSubmission->bug->project_id, UserRole::PROJECT_MANAGER->value) ||
            $user->id == $bugSubmission->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BugSubmission $bugSubmission): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BugSubmission $bugSubmission): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BugSubmission $bugSubmission): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BugSubmission $bugSubmission): bool
    {
        return false;
    }
}
