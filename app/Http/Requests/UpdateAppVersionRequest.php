<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppVersionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'latest_version' => ['sometimes', 'string', 'max:20'],
            'min_supported_version' => ['sometimes', 'string', 'max:20'],
            'force_update' => ['sometimes', 'boolean'],
            'update_url' => ['sometimes', 'url', 'max:500'],
        ];
    }
}
