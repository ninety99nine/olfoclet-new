<?php

namespace App\Http\Requests\Deployment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeploymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('deployment'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'country'                  => ['sometimes', 'string', 'size:2'],
            'network'                  => ['sometimes', 'string', 'max:40'],
            'active'                   => ['sometimes', 'boolean'],
            'individual_replies'       => ['sometimes', 'boolean'],
            'max_character_length'     => ['sometimes', 'integer', 'min:1', 'max:182'],
            'incoming_format'          => ['sometimes', 'in:xml,json'],
            'transform_request_language' => ['sometimes', 'in:php,python,javascript'],
            'transform_request_code'   => ['sometimes', 'string'],
            'outgoing_format'          => ['sometimes', 'in:xml,json,plain text'],
            'transform_response_language' => ['sometimes', 'in:php,python,javascript'],
            'transform_response_code'  => ['sometimes', 'string'],
        ];
    }
}
