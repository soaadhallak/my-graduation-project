<?php

namespace App\Rules;

use App\Models\Invitation;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;
use Illuminate\Translation\PotentiallyTranslatedString;

class TeamInvitationAcceptRule implements ValidationRule
{
    public function __construct(
        protected ?User $user
    )
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $invitation = Invitation::where('token', $value)->first();

        if(!$invitation) {
            $fail(__('Invalid token'));
            return;
        }

        if($invitation->status !== 'pending') {
            $fail("This invitation has already been accepted or declined {$invitation->status}".$invitation->status);
            return;
        }

        if($invitation->expires_at->isPast()) {
            $fail(__('This invitation has expired'));
            return;
        }

    }
}
