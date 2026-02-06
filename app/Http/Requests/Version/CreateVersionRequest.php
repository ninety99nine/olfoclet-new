<?php

namespace App\Http\Requests\Version;

use App\Models\Version;
use Illuminate\Foundation\Http\FormRequest;

class CreateVersionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', [Version::class, $this->route('app')]);
    }

    /**
     * Get the validation rules that versionly to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'clone_from_id' => ['nullable', 'uuid', 'exists:versions,id']
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'number.required' => 'The version number is required.',
            'number.string' => 'The version number must be a string.',
            'number.max' => 'The version number must not exceed 255 characters.',
            'description.string' => 'The version description must be a string.',
            'description.max' => 'The version description must not exceed 500 characters.'
        ];
    }
}
