<?php

namespace App\Http\Requests\Version;

use App\Models\Version;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVersionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', [Version::class, $this->route('app'), $this->route('version')]);
    }

    /**
     * Get the validation rules that versionly to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'builder' => ['sometimes', 'json'],
            'number' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
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
            'number.string' => 'The version number must be a string.',
            'number.max' => 'The version number must not exceed 255 characters.',
            'description.string' => 'The version description must be a string.',
            'description.max' => 'The version description must not exceed 500 characters.'
        ];
    }
}
