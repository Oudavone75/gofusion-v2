<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ChangeChallengeStatusRequest extends FormRequest
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
            'user_id' => 'sometimes|exists:users,id',
            'go_session_step_id' => 'sometimes|exists:go_session_steps,id',
            'guideline_text' => 'sometimes|string|max:255',
            'points' => 'sometimes|integer|min:1|max:300',
            'description' => 'sometimes|string',
        ];
    }
}
