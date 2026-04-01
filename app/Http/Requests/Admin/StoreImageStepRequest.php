<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreImageStepRequest extends FormRequest
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
        $create_route = request()->is('admin/images/create') || request()->is('company-admin/steps/images/create');
        $mode = request()->input('mode');
        return [
            'type' => $admin_route ? 'required|in:campaign,season' : ['nullable'],
            'company' => $admin_route ? 'required_if:type,campaign|string|max:255|exists:companies,id' : ['nullable'],
            'campaign' => 'required|exists:campaigns_seasons,id',
            'session' => 'required|exists:go_sessions,id',
            'title' => 'required|string|max:255',
            'mode' => 'required|in:photo,video,checkbox',
            'guideline_text' => $mode == 'photo' ? 'required|string|max:255' : 'nullable|string|max:255',
            'description' => 'nullable|string',
            'points' => 'required|integer|min:1|max:300',
            'image' => ($mode == 'photo' && $create_route) ? 'required|image|mimes:jpeg,png,jpg,webp|max:2048' : 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'video_url' => $mode == 'video' ? 'required|url' : 'nullable|url',
            'keywords' => $mode == 'video' ? 'required|array|min:1' : 'nullable|array',
            'keywords.*' =>$mode == 'video' ? 'string|max:255' : 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'image' => 'Image must be a file of type: jpeg, png, jpg.',
            'guideline_text.required' => 'AI description is required.',
            'guideline_text.max' => 'AI description must be shorter than 255 characters.',
            'keywords.required' => 'Please add at least one keyword.',
            'keywords.min' => 'Please add at least one keyword.',
            'keywords.*.required' => 'Please add at least one keyword.',
        ];
    }
}
