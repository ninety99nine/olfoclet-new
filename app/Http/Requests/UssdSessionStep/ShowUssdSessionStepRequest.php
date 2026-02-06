<?php

namespace App\Http\Requests\UssdSessionStep;

use App\Models\UssdSessionStep;
use Illuminate\Foundation\Http\FormRequest;

class ShowUssdSessionStepRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('view', [UssdSessionStep::class, $this->route('app'), $this->route('ussd_session'), $this->route('ussd_session_step')]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
