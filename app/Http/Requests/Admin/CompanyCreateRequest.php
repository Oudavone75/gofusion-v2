<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CompanyCreateRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:companies,name',
            'email' => 'required|email|max:255|unique:companies,email|unique:users,email',
            'address' => 'required|string|max:500',
            'registration_date' => 'required|date|before_or_equal:today',
            'type' => 'required|exists:modes,id',
            'department' => 'required|array',
            'department.*' => 'required|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required.',
            'name.unique' => 'Name already exists.',
            'email.required' => 'Email is required.',
            'email.unique' => 'Email already exists.',
            'address.required' => 'Address is required.',
            'registration_date.required' => 'Registration Date is required.',
            'type.required' => 'Type is required.',
            'department.required' => 'At least one department is required.',
            'department.*.required' => 'Department name is required.',
            'department.*.max' => 'Department name may not be greater than 255 characters.',
            'image' => 'Image must be a file of type: jpeg, png, jpg.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $errors = $validator->errors();

            // Loop through current error keys
            foreach ($errors->messages() as $key => $messages) {
                if (preg_match('/^department\.\d+$/', $key)) {
                    // Replace "department.0" with "department[]"
                    $errors->add('department[]', $messages[0]);
                    $errors->forget($key);
                }
            }
        });
    }

}
