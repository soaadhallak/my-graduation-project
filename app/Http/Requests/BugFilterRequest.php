<?php

namespace App\Http\Requests;

use App\Enums\BugEnvironments;
use App\Enums\BugPriorities;
use App\Enums\BugStatuses;
use App\Rules\IsProjectMember;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BugFilterRequest extends FormRequest
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
        $project = $this->route('project');
        $rules = [
            'filter' => ['sometimes', 'array'],
            'filter.status' => ['sometimes', Rule::enum(BugStatuses::class)],
            'filter.priority' => ['sometimes', Rule::enum(BugPriorities::class)],
            'filter.environment' => ['sometimes', Rule::enum(BugEnvironments::class)],
            'filter.search' => ['sometimes', 'string', 'max:255'],
            'sort' => ['sometimes', 'string', Rule::in(['created_at', 'priority', 'title', 'updated_at'])],
            'perPage' => ['sometimes', 'integer', 'between:1,100']  
        ];

        if ($project) {
            $rules['filter.projectId'] = ['prohibited'];
            $rules['filter.assignedTo'] = ['sometimes', 'integer', 'exists:users,id', new IsProjectMember($project->id)];
            $rules['filter.creatorId']  = ['sometimes', 'integer', 'exists:users,id', new IsProjectMember($project->id)];
        } else {
            $rules['filter.projectId'] = ['sometimes', 'integer', 'exists:projects,id'];
        }
    Log::info(json_encode($rules));
        return $rules;
    }
}
