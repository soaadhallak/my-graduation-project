<?php

namespace App\Rules;

use App\Enums\UserRole;
use App\Models\ProjectUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CannotDemoteLastProjectManager implements ValidationRule
{
    public function __construct(
        protected ?ProjectUser $membership,
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->membership) {
            return;
        }

        if (
            $this->membership->role === UserRole::PROJECT_MANAGER->value
            && $value !== UserRole::PROJECT_MANAGER->value
        ) {
            $fail(__('Cannot change the role of the project manager.'));
        }

        if ($value === UserRole::PROJECT_MANAGER->value) {
            $hasOtherManager = ProjectUser::where('project_id', $this->membership->project_id)
                ->where('role', UserRole::PROJECT_MANAGER->value)
                ->where('id', '!=', $this->membership->id)
                ->exists();

            if ($hasOtherManager) {
                $fail(__('This project already has a project manager.'));
            }
        }
    }
}
