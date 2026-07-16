<?php

namespace App\Http\Requests;

use App\Enums\BugEnvironments;
use App\Enums\BugPriorities;
use App\Enums\BugStatuses;
use App\Rules\IsProjectMember;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class StoreBugRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $projectId = $this->input('projectId');
       
        return auth()->user()->isMemberOfProject($projectId);
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('assignedTo')) {
            $this->merge([
                'status' => BugStatuses::OPEN->value,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'priority' => ['required', Rule::in(BugPriorities::values())],
            'environment' => ['required', Rule::in(BugEnvironments::values())],
            'projectId' => ['required', 'exists:projects,id'],
            'assignedTo' => [
                'nullable', 
                'exists:users,id',
                Rule::prohibitedIf(function () {
                    $projectId = $this->input('projectId');

                    if (!$projectId) return true;

                    return !auth()->user()->isMemberOfProject($projectId, ['project_manager']);
                }),
                new IsProjectMember($this->input('projectId'))
             ],
            'labels' => ['array'],
            'labels.*' => ['exists:labels,id'],
            'screenshot' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }
}
