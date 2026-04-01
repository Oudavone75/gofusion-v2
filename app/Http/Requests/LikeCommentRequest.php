<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LikeCommentRequest extends FormRequest
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
            'comment_id' => 'required|integer|exists:post_comments,id',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'comment_id.required' => 'Comment ID is required.',
            'comment_id.exists' => 'The selected comment does not exist.',
        ];
    }
}
