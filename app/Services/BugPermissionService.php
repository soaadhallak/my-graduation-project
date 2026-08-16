<?php

namespace App\Services;

use App\Enums\BugStatuses;
use App\Models\Bug;
use App\Models\User;
use App\Strategies\BugUpdateStrategy;
use Illuminate\Support\Facades\Gate;

class BugPermissionService
{
    private const EDIT_FIELDS = [
        'title',
        'description',
        'priority',
        'environment',
        'labels',
        'screenshot',
    ];

    public function for(?User $user, Bug $bug): array
    {
        if (! $user) {
            return $this->denied();
        }

        $allowedFields = (new BugUpdateStrategy($user, $bug))->getAllowedFields();
        $canTest = $this->canTest($user, $bug);

        return [
            'canEdit' => count(array_intersect(self::EDIT_FIELDS, $allowedFields)) > 0,
            'canDelete' => Gate::forUser($user)->allows('delete', $bug),
            'canUpdateStatus' => in_array('status', $allowedFields, true),
            'canAssign' => in_array('assignedTo', $allowedFields, true),
            'canPassTest' => $canTest,
            'canFailTest' => $canTest,
            'canSubmit' => $user->id == $bug->assigned_to,
            'allowedFields' => array_values($allowedFields),
        ];
    }

    private function canTest(User $user, Bug $bug): bool
    {
        $status = $bug->status instanceof BugStatuses
            ? $bug->status->value
            : (string) $bug->status;

        return $user->id == $bug->creator_id
            && $status === BugStatuses::READY_FOR_QA->value;
    }

    private function denied(): array
    {
        return [
            'canEdit' => false,
            'canDelete' => false,
            'canUpdateStatus' => false,
            'canAssign' => false,
            'canPassTest' => false,
            'canFailTest' => false,
            'canSubmit' => false,
            'allowedFields' => [],
        ];
    }
}
