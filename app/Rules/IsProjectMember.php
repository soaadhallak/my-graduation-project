<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Translation\PotentiallyTranslatedString;

class IsProjectMember implements ValidationRule
{
    public function __construct(protected ?int $projectId)
    {
        //
    }
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isMember = DB::table('project_users')
            ->where('project_id', $this->projectId)
            ->where('user_id', $value)
            ->exists();

        if (!$isMember) {
            $fail(__('The selected developer must be a member of this project.'));    
        }
    }
}
