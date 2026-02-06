<?php

namespace App\Http\Requests\UssdSessionFlag;

use App\Models\UssdSessionFlag;
use Illuminate\Foundation\Http\FormRequest;

class CreateUssdSessionFlagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', [UssdSessionFlag::class, $this->route('app')]);
    }

    public function rules(): array
    {
        return [
            'ussd_session_step_id' => ['nullable', 'uuid', 'exists:ussd_session_steps,id'],
            'ussd_session_id' => ['required', 'uuid', 'exists:ussd_sessions,id'],
            'category'        => ['required', 'string', 'in:bug,ux,performance,content,security,feature-request,other'],
            'priority'        => ['required', 'in:low,medium,high,critical'],
            'comment'         => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Please select a category.',
            'category.in'       => 'Invalid category selected.',
            'priority.required' => 'Please select a priority.',
            'priority.in'       => 'Invalid priority selected.',
            'ussd_session_id.exists' => 'The selected session does not exist.',
        ];
    }
}
