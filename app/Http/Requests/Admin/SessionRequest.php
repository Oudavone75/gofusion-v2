<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SessionRequest extends FormRequest
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
            'type' => 'required|in:campaign,season',
            'company' => 'required_if:type,campaign|exists:companies,id',
            'campaign' => 'required|exists:campaigns_seasons,id',
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('go_sessions')->where(function ($query) {
                    if ($this->method() === 'PUT') {
                        $query->where('campaign_season_id', request('campaign'));
                        return $query->where('id', '!=', $this->route('session')->id);
                    }
                    return $query->where('campaign_season_id', request('campaign'));
                }),
            ]
        ];
    }
}
