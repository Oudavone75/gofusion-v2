<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class FormResponseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        // Format the response as you want
        throw new HttpResponseException(
            response()->json([
                'status' => false,
                'message' => $errors->first(),
                'result' => [],
                'code' => 422
            ], 422)
        );
    }
}
