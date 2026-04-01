<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSurveyFeedbackRequest extends FormRequest
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
            'points' => 'required|integer|numeric|min:1|max:300',
            'questions' => 'required|array|min:1|max:10',
            'questions.*.text' => 'required|string',
            'questions.*.options' => 'required|array|min:2|max:5',
            'questions.*.options.*' => 'required|string',
        ];
    }

     public function messages(): array
    {
        return [
            'title.required' => 'Survey title is required',
            'description.required' => 'Survey description is required',
            'points.required' => 'Total points is required',
            'points.min' => 'Total points must be at least 1',
            'points.max' => 'Total points cannot exceed 300',
            'questions.required' => 'At least two questions are required for the survey.',
            'questions.min' => 'You must add at least two questions.',
            'questions.max' => 'Maximum 10 questions allowed',
            'questions.*.text.required' => 'Question text is required',
            'questions.*.options.required' => 'Question options are required',
            'questions.*.options.min' => 'Each question must have at least 2 options',
            'questions.*.options.max' => 'Each question can have maximum 5 options',
            'questions.*.options.*.required' => 'Option text is required',
        ];
    }
}
