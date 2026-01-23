<?php

namespace App\Http\Requests\UssdAccount;

use App\Models\UssdAccount;
use Illuminate\Foundation\Http\FormRequest;

class DeleteUssdAccountsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('deleteAny', UssdAccount::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'ussd_account_ids' => ['required', 'array', 'min:1'],
            'ussd_account_ids.*' => ['uuid', 'exists:ussd_accounts,id'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'ussd_account_ids.required' => 'The account IDs are required.',
            'ussd_account_ids.array' => 'The account IDs must be an array.',
            'ussd_account_ids.min' => 'At least one account ID is required.',
            'ussd_account_ids.*.uuid' => 'Each account ID must be a valid UUID.',
            'ussd_account_ids.*.exists' => 'One or more account IDs do not exist.',
        ];
    }
}
