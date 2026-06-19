<?php

namespace App\Rules;

use App\Enums\BugStatuses;
use App\Models\Bug;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class BugStatusMatchesRule implements ValidationRule
{
    public function __construct(
        protected Bug $bug,
        protected BugStatuses|string $expectedStatus,
    ) {
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $currentStatus = $this->bug->status instanceof BugStatuses
            ? $this->bug->status->value
            : (string) $this->bug->status;

        $expectedStatus = $this->expectedStatus instanceof BugStatuses
            ? $this->expectedStatus->value
            : $this->expectedStatus;

        if ($currentStatus !== $expectedStatus) {
            $fail(__('The bug status must be :expected. Current status is :current.', [
                'expected' => $expectedStatus,
                'current' => $currentStatus,
            ]));
        }
    }
}
