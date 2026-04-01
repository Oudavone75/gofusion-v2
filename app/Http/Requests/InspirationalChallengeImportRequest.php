<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InspirationalChallengeImportRequest extends FormRequest
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
            'theme_id'      => 'required|exists:themes,id',
            'company_id'    => 'nullable|exists:companies,id|required_if:type,for_company',
            'departments'   => 'required_if:type,campaign|array|min:1',
            'departments.*' => 'exists:company_departments,id',
            'file'          => ['required', 'file', 'mimes:xls,xlsx'],
        ];
    }

    public function messages()
    {
        return [
            'company_id.required_if'  => 'The company field is required.',
            'departments.required_if' => 'Please select at least one department.',
            'departments.min'         => 'Please select at least one department.',
            'departments.*.exists'    => 'One or more selected departments are invalid.',
        ];
    }
}
