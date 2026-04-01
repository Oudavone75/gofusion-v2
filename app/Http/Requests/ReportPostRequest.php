<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportPostRequest extends FormRequest
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
            'reason' => 'required|string',
            'description' => 'nullable|string|max:500',
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
            'reason.required' => 'Reason for reporting is required.',
            'description.max' => 'Description must not exceed 500 characters.',
        ];
    }

    /**
     * Custom validation to prevent self-reporting
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->post_id) {
                $post = \App\Models\Post::with('author')->find($this->post_id);
                $user = \Illuminate\Support\Facades\Auth::user();

                // Prevent users from reporting their own posts
                if ($post && $user && $post->author_id == $user->id && $post->author_type == get_class($user)) {
                    $validator->errors()->add('post_id', __('general.cannot_report_own_post'));
                }

                // Check if user already reported this post
                if ($user) {
                    if ($user->isEmployee() && $post->author->is_admin == true) {
                        $validator->errors()->add('post_id', __('general.admin_post_report'));
                    }
                    $existingReport = \App\Models\PostReport::where('post_id', $this->post_id)
                        ->where('reported_by', $user->id)
                        ->exists();

                    if ($existingReport) {
                        $validator->errors()->add('post_id', __('general.report_exists'));
                    }
                    $adminPost = \App\Models\Post::where('id', $this->post_id)
                        ->where('author_type', 'App\Models\Admin')
                        ->exists();
                    if ($adminPost) {
                        $validator->errors()->add('post_id', __('general.admin_post_report'));
                    }
                }
            }
        });
    }
}
