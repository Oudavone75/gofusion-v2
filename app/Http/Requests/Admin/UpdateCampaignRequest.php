<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'from_ranking' => 'required|array',
            'to_ranking' => 'required|array',
            'reward' => 'required|array',
            'custom_reward' => 'nullable|string|max:1000',
            'from_ranking.*' => 'required',
            'to_ranking.*' => 'required',
            'reward.*' => 'required',
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
            'description.max' => 'Description cannot exceed 1000 characters.',
            'custom_reward.max' => 'Custom reward cannot exceed 1000 characters.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $fromRanking = $this->input('from_ranking', []);
            $toRanking = $this->input('to_ranking', []);
            $reward = $this->input('reward', []);

            if (count($fromRanking) !== count($toRanking) || count($fromRanking) !== count($reward)) {
                $validator->errors()->add('array_length', 'The From ranking, To ranking, and Reward arrays must have the same number of items.');
            }
        });
    }
}
