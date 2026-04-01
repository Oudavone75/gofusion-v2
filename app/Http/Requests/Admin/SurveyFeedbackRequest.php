<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SurveyFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $admin_route = request()->is('admin/*');

        $rules = [
            'type' => $admin_route ? 'required|in:campaign,season' : ['nullable'],
            'company' => $admin_route ? ['required_if:type,campaign', 'exists:companies,id'] : ['nullable'],
            'campaign' => ['required', 'exists:campaigns_seasons,id'],
            'session' => ['required', 'exists:go_sessions,id'],
            'points' => ['required', 'numeric', 'min:1', 'max:300'],
        ];

        $rules['questions'] = ['required', 'array', 'min:1', 'max:10'];
        $rules['questions.*.text'] = ['required', 'string'];
        $rules['questions.*.options'] = ['required', 'array', 'min:2', 'max:5'];
        $rules['questions.*.options.*'] = ['required', 'string'];

        return $rules;
    }

    public function messages()
    {
        return [
            'company.required' => 'Please select a company.',
            'campaign.required' => 'Please select a campaign.',
            'session.required' => 'Please select a session.',
            'title.required' => 'Please enter a title.',
            'description.required' => 'Please enter a description.',
            'points.required' => 'Please enter points.',
            'points.min' => 'Points must be at least 1.',
            'points.max' => 'Points cannot exceed 300.',
            'questions.required' => 'At least two questions are required for the survey.',
            'questions.min' => 'You must add at least two questions.',
            'questions.*.options.min' => 'Each question must have at least two options.',
            'questions.*.options.max' => 'Each question can have maximum five options.'
        ];
    }
}
