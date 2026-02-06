<?php

namespace App\Http\Requests\Version;

use App\Models\Version;
use Illuminate\Foundation\Http\FormRequest;

class DeleteVersionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', Version::class);
    }

    /**
     * Get the validation rules that versionly to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'version_ids' => ['required', 'array', 'min:1'],
            'version_ids.*' => ['uuid'],
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
            'version_ids.required' => 'The version IDs are required.',
            'version_ids.array' => 'The version IDs must be an array.',
            'version_ids.min' => 'At least one version ID is required.',
            'version_ids.*.uuid' => 'Each version ID must be a valid UUID.',
        ];
    }
}
