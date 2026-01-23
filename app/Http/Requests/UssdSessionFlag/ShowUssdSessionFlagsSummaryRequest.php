<?php

namespace App\Http\Requests\UssdSessionFlag;

use App\Models\UssdSessionFlag;
use Illuminate\Foundation\Http\FormRequest;

class ShowUssdSessionFlagsSummaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', UssdSessionFlag::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
