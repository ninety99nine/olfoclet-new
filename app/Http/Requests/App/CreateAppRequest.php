<?php

namespace App\Http\Requests\App;

use App\Models\App;
use Illuminate\Foundation\Http\FormRequest;

class CreateAppRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', App::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'size:2'],
            'network' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:5120'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The app name is required.',
            'name.string' => 'The app name must be a string.',
            'name.max' => 'The app name must not exceed 255 characters.',
            'country.required' => 'The app country is required.',
            'country.string' => 'The app country must be a string.',
            'country.size' => 'The app country must 2 characters.',
            'network.required' => 'The app network is required.',
            'network.string' => 'The app network must be a string.',
            'network.max' => 'The app network must not exceed 255 characters.',
            'logo.file' => 'The logo must be a valid file.',
            'logo.mimes' => 'The logo must be a JPEG, PNG, JPG, GIF, or SVG.',
            'logo.max' => 'The logo size must not exceed 5MB.',
        ];
    }
}
