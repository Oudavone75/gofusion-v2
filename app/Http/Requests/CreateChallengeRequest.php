<?php

namespace App\Http\Requests;

use App\Http\Requests\FormResponseRequest;

class CreateChallengeRequest extends FormResponseRequest
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
        $rules = $this->commonRules();

        if ($this->challenge_category_id == 2) {
            $rules = [...$rules, ...$this->eventRules()];
        }

        return $rules;
    }

    public function commonRules()
    {
        return [
            'go_session_step_id' => 'required|exists:go_session_steps,id',
            'challenge_category_id' => 'required|exists:challenge_categories,id',
            'title' => 'required|string',
            'description' => 'required|string',
            'image' => 'nullable|mimes:jpg,jpeg,png,tiff,webp|max:6072',
            'theme_id' => 'required|exists:themes,id'
        ];
    }

    public function eventRules()
    {
        return [
            'event_type' => ['required', 'string', 'in:onsite,online'],
            'event_start_date' => ['required', 'date', 'before_or_equal:event_end_date'],
            'event_end_date' => ['required', 'date', 'after_or_equal:event_start_date'],
            'event_location' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'theme_id.exists' => 'The selected theme id is invalid or not exists.'
        ];
    }
}
