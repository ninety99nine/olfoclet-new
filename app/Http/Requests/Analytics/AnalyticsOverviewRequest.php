<?php

namespace App\Http\Requests\Analytics;

use Illuminate\Foundation\Http\FormRequest;

class AnalyticsOverviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'app_id'    => 'nullable|exists:apps,id',
            'range'     => 'sometimes|in:today,7d,30d,90d,ytd,custom',
            'start'     => 'required_if:range,custom|date',
            'end'       => 'required_if:range,custom|date|after_or_equal:start',
            // add country, network, simulated, etc. if needed
        ];
    }
}
