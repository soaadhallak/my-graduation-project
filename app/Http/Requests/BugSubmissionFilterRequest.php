<?php

namespace App\Http\Requests;

use App\Enums\BugSubmissionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BugSubmissionFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'filter' => ['sometimes', 'array'],
            'filter.status' => ['sometimes', Rule::enum(BugSubmissionStatus::class)],
            'filter.projectId' => ['sometimes', 'integer', 'exists:projects,id'],
            'filter.bugId' => ['sometimes', 'integer', 'exists:bugs,id'],
            'filter.submittedBy' => ['sometimes', 'integer', 'exists:users,id'],
            'sort' => ['sometimes', 'string', Rule::in(['created_at', 'updated_at', 'status', '-created_at', '-updated_at', '-status'])],
            'perPage' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }
}
