<?php

namespace App\Http\Requests\UssdAccount;

use App\Models\UssdAccount;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowUssdAccountsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', UssdAccount::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'simulated' => ['nullable', 'boolean'],
            'has_flags' => ['nullable', 'boolean'],
            'date_range_end' => ['nullable', 'date'],
            'date_range_start' => ['nullable', 'date'],
            'country' => ['nullable', 'string', 'size:2'],
            'network' => ['nullable', 'string', 'max:40'],
            'app_id' => ['nullable', 'uuid', 'exists:apps,id'],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'blocked'])],
            'association' => ['nullable', Rule::in(['super_admin', 'admin', 'user'])],
            'date_range' => ['nullable', Rule::in(['today', 'this_week', 'this_month', 'this_year', 'custom'])],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('simulated')) {
            $this->merge(['simulated' => filter_var($this->simulated, FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->has('has_flags')) {
            $this->merge(['has_flags' => filter_var($this->has_flags, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
