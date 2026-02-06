<?php

namespace App\Http\Requests\UssdSessionFlag;

use App\Models\UssdSessionFlag;
use Illuminate\Foundation\Http\FormRequest;

class DeleteUssdSessionFlagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('delete', [UssdSessionFlag::class, $this->route('app'), $this->route('ussd_session_flag')]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
