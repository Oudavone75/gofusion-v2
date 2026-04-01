<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
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
            'type' => 'required|in:campaign,season',
            'company_id' => 'nullable|exists:companies,id',
            'departments' => 'required_if:type,campaign|array|min:1',
            'departments.*' => 'exists:company_departments,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'notification_type' => 'required|in:direct,scheduled',
            'scheduled_at' => 'nullable|date|after_or_equal:now'
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_at.after_or_equal' => 'The scheduled time must be in the future or current time.',
            'scheduled_at.date' => 'Please provide a valid date and time for scheduling.',
            'departments.required_if' => 'Please select at least one department.',
            'departments.min'         => 'Please select at least one department.',
            'departments.*.exists'    => 'One or more selected departments are invalid.',
        ];
    }
}
