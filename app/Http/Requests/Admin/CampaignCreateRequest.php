<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampaignCreateRequest extends FormRequest
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
            'company_id' => 'required_if:type,campaign|exists:companies,id',
            'departments' => 'required_if:type,campaign|array|min:1',
            'departments.*' => 'exists:company_departments,id',
            'start_date' => 'required|date|after_or_equal:today|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'required|string|max:1000',
            'from_ranking' => 'required|array',
            'to_ranking' => 'required|array',
            'reward' => 'required|array',
            'from_ranking.*' => 'required',
            'to_ranking.*' => 'required',
            'reward.*' => 'required',
            'custom_reward' => 'nullable|string|max:255',
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('campaigns_seasons')
                    ->where(fn($q) => $q->where('company_id', request('company_id')))
                    ->ignore($id ?? null)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'company.required' => 'Please select a company.',
            'company.exists' => 'Please select a company.',
            'departments.required_if' => 'Please select at least one department.',
            'departments.min'         => 'Please select at least one department.',
            'departments.*.exists'    => 'One or more selected departments are invalid.',
            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Start date must be a valid date.',
            'start_date.before_or_equal' => 'Start date must be before or equal to end date.',
            'end_date.required' => 'End date is required.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'title.required' => 'Please enter a campaign title.',
            'title.max' => 'Title cannot exceed 255 characters.',
            'custom_reward.max' => 'Custom reward cannot exceed 1000 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $errors = $validator->errors();

            foreach ($errors->messages() as $key => $messages) {
                if (preg_match('/^from_ranking\.\d+$/', $key) || preg_match('/^to_ranking\.\d+$/', $key) || preg_match('/^reward\.\d+$/', $key)) {
                    $errors->add('from_ranking[]', 'The From Ranking Field is required.');
                    $errors->add('to_ranking[]', 'The To Ranking Field is required.');
                    $errors->add('reward[]', 'The Reward Field is required.');
                    $errors->forget($key);
                }
            }
        });
    }
}
