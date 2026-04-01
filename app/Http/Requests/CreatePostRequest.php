<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
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
        $type = $this->input('type');
        return [
            'company_id' => $type == 'campaign' ? 'required|exists:companies,id' : 'nullable',
            'content' => 'nullable|string|max:5000|required_without:medias',
            'medias' => 'nullable|array|max:4|required_without:content',
            'medias.*' => [
                'file',
                'mimes:jpeg,jpg,png,webp,mp4,mov,avi,pdf',
                'max:50120', // 50MB
            ],
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            // Content validations
            'content.required_without' => 'Please provide either content or at least one media file.',
            'content.max' => 'Post content must not exceed 5000 characters.',

            // Medias validations
            'medias.required_without' => 'Please provide at least one media file or write some content.',
            'medias.max' => 'You can upload a maximum of 4 media files.',

            // Medias.* validations
            'medias.*.file' => 'Each media item must be a valid file.',
            'medias.*.mimes' => 'Media files must be jpeg, jpg, png, webp, mp4, mov, avi, or pdf.',
            'medias.*.max' => 'Each file must not exceed 50MB.',
        ];
    }
}
