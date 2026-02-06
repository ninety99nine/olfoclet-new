<?php

namespace App\Http\Requests\UssdSession;

use App\Models\UssdSession;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ShowUssdSessionsSummaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', [UssdSession::class, $this->route('app')]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'simulated' => ['nullable', 'boolean'],
            'has_flags' => ['nullable', 'boolean'],
            'successful' => ['nullable', 'boolean'],
            'date_range_end' => ['nullable', 'date'],
            'date_range_start' => ['nullable', 'date'],
            'msisdn' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'size:2'],
            'network' => ['nullable', 'string', 'max:40'],
            'shortcode' => ['nullable', 'string', 'max:16'],
            'date_range' => ['nullable', Rule::in(['today', 'this_week', 'this_month', 'this_year', 'custom'])],
        ];
    }

    /**
     * Prepare the data for validation (optional normalization).
     */
    protected function prepareForValidation()
    {
        if ($this->has('simulated')) {
            $this->merge(['simulated' => filter_var($this->simulated, FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->has('successful')) {
            $this->merge(['successful' => filter_var($this->successful, FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->has('has_flags')) {
            $this->merge(['has_flags' => filter_var($this->has_flags, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
