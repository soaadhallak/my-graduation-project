<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGithubConfigRequest extends FormRequest
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
            'projectId' => ['required', 'exists:projects,id'],
            'githubRepoId' => ['required', 'string', 'unique:github_configs,github_repo_id'],
            'fullName' => ['required', 'string'],
            'installationId' => ['required', 'string'],
            'defaultBranch' => ['nullable', 'string'],
        ];
    }
}
