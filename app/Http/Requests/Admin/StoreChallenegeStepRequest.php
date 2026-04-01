<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreChallenegeStepRequest extends FormRequest
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

        return [
            'type' => $admin_route ? 'required|in:campaign,season'  : ['nullable'],
            'company' => $admin_route ? ['required_if:type,campaign', 'exists:companies,id'] : ['nullable'],
            'campaign' => 'required|exists:campaigns_seasons,id',
            'session' => 'required|exists:go_sessions,id',
            'points' => 'required|integer|min:1|max:300',
        ];
    }
}
