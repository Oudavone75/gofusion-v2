<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'registration_date' => 'required|date',
            'type' => 'required|exists:modes,id',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required.',
            'address.required' => 'Address is required.',
            'registration_date.required' => 'Registration Date is required.',
            'type.required' => 'Type is required.',
            'image' => 'Image must be a file of type: jpeg, png, jpg.',
        ];
    }
}
