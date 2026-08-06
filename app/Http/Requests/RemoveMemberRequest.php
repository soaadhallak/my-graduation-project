<?php

namespace App\Http\Requests;

use App\Rules\CannotRemoveProjectManager;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RemoveMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');
        $projectUser = $this->route('projectUser');

        if (! $project || ! $projectUser) {
            return false;
        }

        return $this->user()->can('removeMember', [$project, $projectUser]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'member' => [
                'required',
                Rule::exists('project_users', 'id')->where('project_id', $project->id),
                new CannotRemoveProjectManager(),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'member' => $this->route('projectUser')?->id ?? $this->route('projectUser'),
        ]);
    }

    public function messages(): array
    {
        return [
            'member.exists' => __('The selected user is not a member of this project.'),
        ];
    }
}
