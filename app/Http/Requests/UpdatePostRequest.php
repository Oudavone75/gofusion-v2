<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
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
            'content' => 'nullable|string|max:5000',
            'medias' => 'nullable|array|max:4',
            'medias.*' => [
                'file',
                'mimes:jpeg,jpg,png,webp,mp4,mov,avi,pdf',
                'max:50120', // 50MB max for all files
            ],
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'content.max' => 'Post content must not exceed 5000 characters.',
            'medias.max' => 'You can upload a maximum of 4 media files.',
            'medias.*.file' => 'Each media item must be a valid file.',
            'medias.*.mimes' => 'Media files must be of type: jpeg, jpg, png, webp, mp4, mov, avi, or pdf.',
            'medias.*.max' => 'Each file must not exceed 50MB.',
        ];
    }
}
