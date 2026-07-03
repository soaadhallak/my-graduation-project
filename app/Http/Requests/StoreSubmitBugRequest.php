<?php

namespace App\Http\Requests;

use App\Models\Bug;
use App\Rules\BugStatusMatchesRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSubmitBugRequest extends FormRequest
{
    protected ?Bug $bug = null;

    protected function prepareForValidation(): void
    {
        if ($this->input('bugId')) {  
            $this->bug = Bug::find($this->input('bugId'));
        }
    }
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {  
        return auth()->user()->id === $this->bug->assigned_to && auth()->user()->isMemberOfProject($this->bug->project_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bugId' => ['required', 'integer', 'exists:bugs,id', new BugStatusMatchesRule($this->bug, 'in_progress')],
            'commitHash' => ['required', 'string', 'max:255'],
            'reviewBranch' => ['required', 'string', 'max:255'],
            'changes' => ['required', 'array', 'min:1'],
            'changes.*.file' => ['required', 'string', 'max:255'],
            'changes.*.diff' => ['required', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Invalid payload',
            'errors' => $validator->errors()
        ], 422));
    }
}
