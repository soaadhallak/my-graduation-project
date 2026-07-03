<?php

namespace App\Http\Requests;

use App\Enums\BugStatuses;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class ApproveBugSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $submission = $this->route('submission');

        if (!$submission) {
            return false;
        }

        $bug = $submission->bug;
    
        if ($bug->status->value !== BugStatuses::IN_REVIEW->value) {
            return false;
        }


        return auth()->user()->isMemberOfProject($bug->project_id, UserRole::PROJECT_MANAGER->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }

}
