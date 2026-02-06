<?php

namespace App\Http\Requests\UssdAccount;

use App\Models\UssdAccount;
use Illuminate\Foundation\Http\FormRequest;

class UnblockUssdAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('unblock', [UssdAccount::class, $this->route('app'), $this->route('ussd_account')]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
