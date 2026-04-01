<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChallengeModeQueryRequest extends FormRequest
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
            'mode' => ['required', 'in:citizen,employee'],
        ];
    }

    public function messages(): array
    {
        return [
            'mode.required' => 'The mode query parameter is required.',
            'mode.in' => 'The mode must be either "citizen" or "employee".',
        ];
    }

    public function validationData()
    {
        return $this->query();
    }
}
