<?php

namespace App\Http\Requests\UssdSessionStep;

use App\Models\UssdSessionStep;
use Illuminate\Foundation\Http\FormRequest;

class ShowUssdSessionStepsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', [UssdSessionStep::class, $this->route('app'), $this->route('ussd_session')]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search'          => ['sometimes', 'string'],
            'ussd_session_id' => ['sometimes', 'uuid', 'exists:ussd_sessions,id'],
            'successful'      => ['sometimes', 'boolean'],
            'paginated'       => ['sometimes', 'boolean'],
            'terminated_by_system' => ['sometimes', 'boolean'],
        ];
    }
}
