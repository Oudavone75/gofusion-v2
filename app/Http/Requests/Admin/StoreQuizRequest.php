<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuizRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $admin_route = request()->is('admin/*');
        $rules = [
            'quiz_type' => ['required', 'in:custom,ai'],
            'type' => $admin_route ? 'required|in:campaign,season' : ['nullable'],
            'company' => $admin_route ? ['required_if:type,campaign', 'exists:companies,id'] : ['nullable'],
            'campaign' => ['required', 'exists:campaigns_seasons,id'],
            'session' => ['required', 'exists:go_sessions,id'],
            'title' => ['required', 'string', 'max:255'],
            'points' => ['required', 'integer', 'min:1', 'max:300'],
        ];

        // Add conditional rules based on quiz type
        if ($this->input('quiz_type') === 'custom' || $this->input('quiz_type') === 'import') {
            $rules['questions'] = ['required', 'array', 'min:2', 'max:10'];
            $rules['questions.*.text'] = ['required', 'string'];
            $rules['questions.*.options'] = ['required', 'array', 'min:4', 'max:5'];
            $rules['questions.*.options.*'] = ['required', 'string'];
            $rules['questions.*.correct'] = ['required', 'integer'];
            $rules['questions.*.explanation'] = ['required', 'string'];
        } else {
            $rules['ai_questions'] = ['required', 'array', 'min:2', 'max:10'];
            $rules['ai_questions.*.text'] = ['required', 'string'];
            $rules['ai_questions.*.options'] = ['required', 'array', 'min:4', 'max:5'];
            $rules['ai_questions.*.options.*'] = ['required', 'string'];
            $rules['ai_questions.*.correct'] = ['required', 'integer'];
            $rules['ai_questions.*.explanation'] = ['required', 'string'];

            // AI generation fields (not stored, just for generation)
            $rules['theme_id'] = ['required_if:quiz_type,ai', 'exists:themes,id'];
            $rules['language'] = ['required_if:quiz_type,ai', 'string'];
            $rules['difficulty'] = ['required_if:quiz_type,ai', 'string'];
            $rules['num_questions'] = ['required_if:quiz_type,ai', 'integer', 'min:1', 'max:10'];
            $rules['num_options'] = ['required_if:quiz_type,ai', 'integer', 'min:4', 'max:5'];
            $rules['ai_rules'] = ['required_if:quiz_type,ai', 'string'];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'quiz_type.required' => 'Please select a quiz type.',
            'company.required' => 'Please select a company.',
            'campaign.required' => 'Please select a campaign.',
            'session.required' => 'Please select a session.',
            'title.required' => 'Please enter a title.',
            'points.required' => 'Please enter points.',
            'points.min' => 'Points must be at least 1.',
            'points.max' => 'Points cannot exceed 300.',
            'questions.required' => 'At least two questions are required for the quiz.',
            'questions.min' => 'You must add at least two questions.',
            'questions.*.options.min' => 'Each question must have at least four options.',
            'questions.*.options.max' => 'Each question can have maximum five options.',
            'ai_questions.required' => 'At least two questions are required for the quiz.',
            'ai_questions.min' => 'You must add at least two questions.',
            'ai_questions.*.options.min' => 'Each question must have at least four options.',
            'ai_questions.*.options.max' => 'Each question can have maximum five options.',
            'theme_id.required_if' => 'Please select a theme.',
            'language.required_if' => 'Please select a language.',
            'difficulty.required_if' => 'Please select a difficulty.',
            'num_questions.required_if' => 'Please select a number of questions.',
            'num_options.required_if' => 'Please select a number of options.',
            'ai_rules.required_if' => 'Please enter AI rules.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('quiz_type') === 'custom') {
                $this->validateQuestionCorrectOptions($validator, 'questions');
            } else {
                $this->validateQuestionCorrectOptions($validator, 'ai_questions');
            }
        });
    }

    protected function validateQuestionCorrectOptions($validator, $questionKey)
    {
        $questions = $this->input($questionKey, []);

        foreach ($questions as $index => $question) {
            $correctOption = $question['correct'] ?? null;
            $optionsCount = count($question['options'] ?? []);

            if (!is_numeric($correctOption)) {
                $validator->errors()->add(
                    "$questionKey.$index.correct",
                    "Please select a correct option for question " . ($index + 1)
                );
                continue;
            }

            if ($correctOption < 0 || $correctOption >= $optionsCount) {
                $validator->errors()->add(
                    "$questionKey.$index.correct",
                    "The selected correct option is invalid for question " . ($index + 1)
                );
            }
        }
    }
}
