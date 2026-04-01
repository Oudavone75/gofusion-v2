<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCommentRequest extends FormRequest
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
            'comment' => 'required|string|max:1000',
            'parent_comment_id' => 'nullable|integer|exists:post_comments,id',
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
            'comment.required' => 'Comment text is required.',
            'comment.max' => 'Comment must not exceed 1000 characters.',
            'parent_comment_id.exists' => 'The parent comment does not exist.',
        ];
    }

    /**
     * Custom validation to ensure parent comment belongs to the same post
     * and prevent 3-level nesting
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->parent_comment_id) {
                $parentComment = \App\Models\PostComment::find($this->parent_comment_id);

                // Check if parent comment belongs to the same post
                if ($parentComment && $parentComment->post_id != $this->post_id) {
                    $validator->errors()->add('parent_comment_id', 'The parent comment does not belong to this post.');
                }

                // Prevent 3-level nesting (replies to replies)
                if ($parentComment && $parentComment->parent_comment_id !== null) {
                    $validator->errors()->add('parent_comment_id', 'Cannot reply to a reply. Only 2-level comments are allowed.');
                }
            }
        });
    }
}
