<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileImportRequest extends FormRequest
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
            'type'      => ['required','in:campaign,season'],
            'company'   => ['required_if:type,campaign', 'exists:companies,id'],
            'campaign'  => ['required', 'exists:campaigns_seasons,id'],
            'session'   => ['required', 'array', 'min:1'],
            'session.*' => ['required', 'distinct', 'exists:go_sessions,id'],
            'file'      => ['required', 'file', 'mimes:xls,xlsx'],
        ];
    }
}
