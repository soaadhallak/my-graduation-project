<?php
namespace App\Strategies;

use App\Enums\BugEnvironments;
use App\Enums\BugPriorities;
use App\Enums\BugStatuses;
use App\Enums\UserRole;
use App\Models\Bug;
use App\Models\User;
use App\Rules\IsProjectMember;
use Illuminate\Validation\Rule;

class BugUpdateStrategy
{
    private array $allowedFields = [];
    private array $rules = [];


    public function __construct(User $user, Bug $bug)
    {
        $this->buildStrategy($user, $bug);
    }

    private function buildStrategy(User $user, Bug $bug): void
    {
        $ruleMap = [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'status' => ['sometimes', Rule::in(BugStatuses::values())],
            'priority' => ['sometimes', Rule::in(BugPriorities::values())],
            'environment' => ['sometimes', Rule::in(BugEnvironments::values())],
            'labels' => ['sometimes', 'array'],
            'labels.*' => ['exists:labels,id'],
            'screenshot' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'assignedTo' => ['sometimes', 'exists:users,id', new IsProjectMember($bug->project_id)]
        ];

        if ($user->isMemberOfProject($bug->project_id, UserRole::PROJECT_MANAGER->value)) {
            $this->allowedFields = array_merge($this->allowedFields, ['assignedTo', 'status']);
        } 
        
        if ($user->id === $bug->creator_id) {
            $this->allowedFields = array_merge($this->allowedFields, ['title', 'description', 'priority', 'environment', 'labels', 'screenshot']);
        }

        if ($user->id === $bug->assigned_to) {
            $this->allowedFields = array_merge($this->allowedFields, ['status']);
        }

        $this->allowedFields = array_unique($this->allowedFields);

        foreach ($this->allowedFields as $field) {
            if (isset($ruleMap[$field])) {
                $this->rules[$field] = $ruleMap[$field];
            }
        }
    }

    public function getAllowedFields(): array
    {
        return $this->allowedFields;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}