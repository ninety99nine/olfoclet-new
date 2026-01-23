<?php

namespace App\Http\Requests\Deployment;

use Illuminate\Foundation\Http\FormRequest;

class DeleteDeploymentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', Deployment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'deployment_ids' => ['required', 'array', 'min:1'],
            'deployment_ids.*' => ['uuid', 'exists:deployments,id'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'deployment_ids.required' => 'The deployment IDs are required.',
            'deployment_ids.array'    => 'The deployment IDs must be an array.',
            'deployment_ids.min'      => 'At least one deployment ID is required.',
            'deployment_ids.*.uuid'   => 'Each deployment ID must be a valid UUID.',
            'deployment_ids.*.exists' => 'One or more deployment IDs do not exist.',
        ];
    }
}
