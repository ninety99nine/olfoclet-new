<?php

namespace App\Http\Requests\Ussd;

use Illuminate\Foundation\Http\FormRequest;

class SimulateUssdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'version_id' => ['required', 'string', 'exists:versions,id'],
            'phone_number' => ['required', 'string'],
            'session_id' => ['nullable', 'string'],
            'text' => ['nullable', 'string']
        ];
    }
}
