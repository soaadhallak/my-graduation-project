<?php

namespace App\Http\Requests;

use App\Enums\BugStatuses;
use App\Enums\UserRole;
use App\Strategies\BugUpdateStrategy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Override;

class UpdateBugRequest extends FormRequest
{

    private BugUpdateStrategy $strategy;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $bug = $this->route('bug');
        $user = Auth::user();

        $this->strategy = new BugUpdateStrategy($user, $bug);
        $allowedFields = $this->strategy->getAllowedFields();
        Log::info('Allowed fields for user ID ' . $user->id . ' on bug ID ' . $bug->id . ': ' . json_encode($allowedFields));
        if (empty($allowedFields)) {
            return false;
        }

        $inputKeys = array_keys($this->except(['_method']));
        $unauthorizedFields = array_diff($inputKeys, $allowedFields);

        if (!empty($unauthorizedFields)) {
            abort(403, 'You are not authorized to update the following fields: ' . implode(', ', $unauthorizedFields));
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        Log::info($this->strategy->getRules());
        return $this->strategy->getRules();
    }

    protected function prepareForValidation(): void
    {
        $bug = $this->route('bug');
        $user = $this->user();

        if ($user->id === $bug->assigned_to && $bug->status->value === BugStatuses::OPEN->value) {
            $this->merge([
                'status' => BugStatuses::IN_PROGRESS->value,
            ]);
        }

        if ($this->has('assignedTo') && $this->input('assignedTo') != $bug->assigned_to) {
            if ($this->input('assignedTo') !== null) {
                $movableStatuses = [BugStatuses::BACKLOG->value, BugStatuses::REOPENED->value];
               
                if (in_array($bug->status->value, $movableStatuses)) {
                    $this->merge([
                        'status' => BugStatuses::OPEN->value,
                    ]);
                }

                if ($bug->status->value === BugStatuses::CHANGES_REQUESTED->value) {
                    $this->merge([
                        'status' => BugStatuses::REOPENED->value,
                    ]);
                }
            }

            if ($this->input('assignedTo') === null && $bug->status->value === BugStatuses::OPEN->value) {
                $this->merge([
                    'status' => BugStatuses::BACKLOG->value,
                ]);
            }
        }
    }
}
