<?php

namespace App\Http\Requests\UssdSessionFlag;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUssdSessionFlagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ussd_session_flag'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ussd_session_step_id' => ['sometimes', 'uuid', 'exists:ussd_session_steps,id'],
            'category' => ['sometimes', 'string', 'max:50'],
            'priority' => ['sometimes', 'in:low,medium,high,critical'],
            'comment'  => ['sometimes', 'string'],
        ];
    }
}
