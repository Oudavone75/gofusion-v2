<?php

namespace App\Http\Requests\CompanyAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChallengeRequest extends FormRequest
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
        $mode = request()->input('mode');
        $rules = $this->commonRules(mode: $mode);

        if ($this->category == 'Event'){
            $rules = [...$rules, ...$this->eventRules()];
        }

        if ($this->category == 'Image'){
            $rules = [...$rules, ...$this->imageRules()];
        }

        return $rules;
    }
    public function commonRules($mode)
    {
        return [
            'campaign' => ['nullable','exists:campaigns_seasons,id'],
            'theme_id' => ['required','exists:themes,id'],
            'company_id' => ['nullable','exists:companies,id'],
            'departments' => 'required_if:type,campaign|array|min:1',
            'departments.*' => 'exists:company_departments,id',
            'challenge_category_id' => ['required','exists:challenge_categories,id'],
            'mode' => 'required|in:photo,video,checkbox',
            'description' => 'nullable|string',
            'guideline_text' => $mode == 'photo' ? 'required|string|max:255' : 'nullable|string|max:255',
            'attempted_points' => 'required|integer|min:1|max:300',
            'image' => $mode == 'photo' ? 'required|image|mimes:jpeg,png,jpg,webp|max:2048' : 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'video_url' => $mode == 'video' ? 'required|url' : 'nullable|url',
        ];
    }
    public function eventRules()
    {
        return [
            'event_name' => ['required','string'],
            'event_type' => ['required','string','in:onsite,online'],
            'event_start_date' => ['required','date','before_or_equal:event_end_date'],
            'event_end_date' => ['required','date','after_or_equal:event_start_date'],
            'event_location' => ['required','string'],
        ];
    }
    public function imageRules()
    {
        return [
            'title' => ['required','string']
        ];
    }

    public function messages(): array
    {
        return [
            'campaign.exists'   => 'Campaign is invalid!',
            'theme_id.required'      => 'Please select theme',
            'theme_id.exists'      => 'Theme is invalid!',
            'company_id.required' => 'Please select a company',
            'company_id.exists'   => 'Company is invalid!',
            'departments.required_if' => 'Please select at least one department.',
            'departments.min'         => 'Please select at least one department.',
            'departments.*.exists'    => 'One or more selected departments are invalid.',
            'challenge_category_id.required' => 'Please select a category',
            'challenge_category_id.exists'    => 'Category is invalid!',
            'title.required' => 'Please enter title',
            'description.required' => 'Please enter description',
            'guideline_text.required' => 'AI description is required.',
            'guideline_text.max' => 'AI description must be shorter than 255 characters.',
            'attempted_points.required' => 'Please enter points',
            'attempted_points.integer' => 'Please enter valid points',
            'attempted_points.min' => 'Please enter points between 1 to 300',
            'attempted_points.max' => 'Please enter points between 1 to 300',
            'image.required' => 'Please select image',
            'image.image' => 'Please select valid image',
            'image.mimes' => 'Please select valid image',
        ];
    }
}
