<?php

namespace App\Http\Requests\BusinessKpiRecord;

use App\Models\BusinessKpiRecord;
use Illuminate\Foundation\Http\FormRequest;

class ShowBusinessKpiRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('view', [BusinessKpiRecord::class, $this->route('app'), $this->route('business_kpi'), $this->route('business_kpi_record')]);
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
