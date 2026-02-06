<?php

namespace App\Http\Requests\BusinessKpi;

use App\Models\BusinessKpi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessKpiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', [BusinessKpi::class, $this->route('app'), $this->route('business_kpi')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'metric_type' => ['sometimes', 'required', 'string', Rule::in(['count', 'money', 'percentage', 'distribution'])],
            'chart_type' => ['sometimes', 'required', 'string', Rule::in(['line', 'bar', 'doughnut'])],
            'break_down_by_network' => ['boolean'],
            'break_down_by_country' => ['boolean'],
            'breakdown_display' => ['nullable', 'string', Rule::in(['time', 'distribution'])],
            'currency' => ['nullable', 'string', 'max:10'],
            'distribution_options' => ['nullable', 'array'],
            'distribution_options.*' => ['string', 'max:255'],
            'x_axis_name' => ['nullable', 'string', 'max:255'],
            'y_axis_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'metric_type' => 'metric type',
            'chart_type' => 'chart type',
            'break_down_by_network' => 'break down by network',
            'break_down_by_country' => 'break down by country',
            'breakdown_display' => 'breakdown display',
            'distribution_options' => 'distribution options',
            'x_axis_name' => 'X-axis name',
            'y_axis_name' => 'Y-axis name',
        ];
    }
}
