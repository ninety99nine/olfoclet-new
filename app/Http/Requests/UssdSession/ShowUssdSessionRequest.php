<?php

namespace App\Http\Requests\UssdSession;

use Illuminate\Foundation\Http\FormRequest;

class ShowUssdSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->route('ussd_session'));
    }

    public function rules(): array
    {
        return [];
    }
}
