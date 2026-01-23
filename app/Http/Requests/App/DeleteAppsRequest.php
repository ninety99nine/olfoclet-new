<?php

namespace App\Http\Requests\App;

use App\Models\App;
use Illuminate\Foundation\Http\FormRequest;

class DeleteAppsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', App::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'app_ids' => ['required', 'array', 'min:1'],
            'app_ids.*' => ['uuid'],
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
            'app_ids.required' => 'The app IDs are required.',
            'app_ids.array' => 'The app IDs must be an array.',
            'app_ids.min' => 'At least one app ID is required.',
            'app_ids.*.uuid' => 'Each app ID must be a valid UUID.',
        ];
    }
}
