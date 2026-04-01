<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class SubAdminRequest extends FormRequest
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
    public function rules()
    {
        $guard = $this->input('type', 'admin');
        $table = $guard === 'admin' ? 'admins' : 'users';

        return [
            'name' => 'required|string|max:255',
            'email' => "sometimes|email|unique:{$table},email",
            'password' => [
                'sometimes',
                Password::min(8)
                    ->max(16)
                    ->letters()
                    ->symbols()
                    ->mixedCase()
                    ->numbers(),
            ],
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where('guard_name', $guard),
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'The password field is required.',
            'role.required' => 'Please select a role.',
            'role.exists' => 'The selected role is invalid.',
            'permissions.array' => 'Permissions must be an array.',
            'permissions.*.exists' => 'One or more selected permissions are invalid.',
        ];
    }
}
