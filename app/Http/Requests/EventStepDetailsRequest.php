<?php

namespace App\Http\Requests;

use App\Http\Requests\FormResponseRequest;

class EventStepDetailsRequest extends FormResponseRequest
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
            'go_session_step_id' => [
                'required',
                'integer',
                'exists:go_session_steps,id',
                function ($attribute, $value, $fail) {
                    $step = \App\Models\GoSessionStep::find($value);
                    if (!$step || $step->status !== 'active') {
                        $fail('The selected go session step must have an active status.');
                    }
                },
            ],
        ];
    }
}
