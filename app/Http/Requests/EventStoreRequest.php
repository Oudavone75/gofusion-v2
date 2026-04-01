<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EventStoreRequest extends FormRequest
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
            'title' => 'nullable|max:255',
            'description' => 'nullable',
            'imageUrl' => 'nullable',
            'address' => 'nullable',
            'start_date' => 'nullable',
            'end_date' => 'nullable'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            $first_message = collect($validator->errors()->all())->first();
            throw new HttpResponseException(response()->json([
                'status' => false,
                'message' => $first_message,
                'result' => [],
                'code' => 422
            ], 422));
        }

        // fallback to default behavior (e.g., redirect back for web requests)
        parent::failedValidation($validator);
    }
}
