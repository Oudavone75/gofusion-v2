<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventStepRequest extends FormRequest
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
        $admin_route = request()->is('admin/*');
        return [
            'type' => $admin_route ? 'required|in:campaign,season' : ['nullable'],
            'company' =>  $admin_route ? 'required_if:type,campaign|string|max:255|exists:companies,id' : ['nullable'],
            'campaign' => 'required|exists:campaigns_seasons,id',
            'session' => 'required|exists:go_sessions,id',
            'event_name' => 'required|string',
            'event_type' => 'required|string|in:onsite,online',
            'event_start_date' => 'required|date|after_or_equal:today|before_or_equal:event_end_date',
            'event_end_date' => 'required|date|after_or_equal:event_start_date',
            'event_location' => 'required|string',
            'points' => 'required|integer|min:1|max:300',
            'guideline_text' => 'required|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
           'image' => 'Image must be a file of type: jpeg, png, jpg.',
            'guideline_text.required' => 'AI description is required.',
            'guideline_text.max' => 'AI description must be shorter than 255 characters.',
            'event_id' => 'Event field is required.',
        ];
    }
}
