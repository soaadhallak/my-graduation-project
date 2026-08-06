<?php

namespace App\Rules;

use App\Enums\UserRole;
use App\Models\ProjectUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CannotRemoveProjectManager implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isProjectManager = ProjectUser::where('id', $value)
            ->where('role', UserRole::PROJECT_MANAGER->value)
            ->exists();

        if ($isProjectManager) {
            $fail(__('Cannot remove the project manager from the project.'));
        }
    }
}
