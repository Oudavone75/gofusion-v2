<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReactToPostRequest extends FormRequest
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
            'post_id' => 'required|integer|exists:posts,id',
            'reaction_type' => 'nullable|string|in:❤️,👍,🤗,😂,😮,😢,😡',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'post_id.required' => 'Post ID is required.',
            'post_id.exists' => 'The selected post does not exist.',
            'reaction_type.in' => 'Invalid reaction type. Allowed reactions: ❤️, 👍, 🤗, 😂, 😮, 😢, 😡',
        ];
    }
}
