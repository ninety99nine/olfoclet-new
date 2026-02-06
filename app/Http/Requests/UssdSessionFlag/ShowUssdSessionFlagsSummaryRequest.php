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
        return $this->user()->can('viewAny', [UssdSessionFlag::class, $this->route('app')]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'search'     => ['sometimes', 'string'],
            'status'     => ['sometimes', 'in:open,resolved'],
            'priority'   => ['sometimes', 'in:low,medium,high,critical'],
            'category'   => ['sometimes', 'string'],
            'ussd_session_id' => ['sometimes', 'uuid', 'exists:ussd_sessions,id'],
        ];
    }
}
