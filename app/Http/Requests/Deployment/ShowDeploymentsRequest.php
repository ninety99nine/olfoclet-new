<?php

namespace App\Http\Requests\Deployment;

use App\Models\Deployment;
use Illuminate\Foundation\Http\FormRequest;

class ShowDeploymentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', [Deployment::class, $this->route('app')]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'country'  => ['sometimes', 'string', 'size:2'],
            'network'  => ['sometimes', 'string'],
            'active'   => ['sometimes', 'boolean'],
        ];
    }
}
