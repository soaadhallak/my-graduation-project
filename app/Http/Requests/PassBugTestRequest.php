<?php

namespace App\Http\Requests;

use App\Enums\BugStatuses;
use App\Enums\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class PassBugTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $bug = $this->route('bug');

        if (!$bug) {
            return false;
        }

        if ($bug->status->value != BugStatuses::READY_FOR_QA->value) {
            return false;
        }

        return auth()->user()->id == $bug->creator_id;
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
