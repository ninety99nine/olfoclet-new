<?php

namespace App\Http\Requests\Version;

use App\Models\Version;
use Illuminate\Foundation\Http\FormRequest;

class DeleteVersionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('delete', [Version::class, $this->route('app'), $this->route('version')]);
    }

    /**
     * Get the validation rules that versionly to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
