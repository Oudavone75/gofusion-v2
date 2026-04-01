<?php

namespace App\Http\Requests\CompanyAdmin;

use Illuminate\Foundation\Http\FormRequest;

class CampaignRequest extends FormRequest
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
            'departments' => 'required|array|min:1',
            'departments.*' => 'exists:company_departments,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today|before:end_date',
            'end_date' => 'required|date|after:start_date',
            'from_ranking' => 'required|array',
            'to_ranking' => 'required|array',
            'reward' => 'required|array',
            'from_ranking.*' => 'required',
            'to_ranking.*' => 'required',
            'reward.*' => 'required',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $errors = $validator->errors();

            foreach ($errors->messages() as $key => $messages) {
                if (preg_match('/^from_ranking\.\d+$/', $key) || preg_match('/^to_ranking\.\d+$/', $key) || preg_match('/^reward\.\d+$/', $key)) {
                    $errors->add('from_ranking[]', 'The From Ranking Field is required.');
                    $errors->add('to_ranking[]', 'The To Ranking Field is required.');
                    $errors->add('reward[]', 'The Reward Field is required.');
                    $errors->forget($key);
                }
            }
        });
    }
}
