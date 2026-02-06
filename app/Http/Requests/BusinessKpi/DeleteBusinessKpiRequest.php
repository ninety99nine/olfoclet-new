<?php

namespace App\Http\Requests\BusinessKpi;

use App\Models\BusinessKpi;
use Illuminate\Foundation\Http\FormRequest;

class DeleteBusinessKpiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('delete', [BusinessKpi::class, $this->route('app'), $this->route('business_kpi')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
