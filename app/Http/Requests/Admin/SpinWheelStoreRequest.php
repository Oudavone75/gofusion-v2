<?php

namespace App\Http\Requests\Admin;

use App\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SpinWheelStoreRequest extends FormRequest
{
    use ApiResponse;
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
            'type' => $admin_route ? 'required|in:campaign,season' : ['nullable'],
            'company' => $admin_route ? ['required_if:type,campaign', 'exists:companies,id'] : ['nullable'],
            'campaign' => ['required', 'exists:campaigns_seasons,id'],
            'session' => ['required', 'exists:go_sessions,id'],
            'video_url' => ['required', 'string', 'max:255'],
            'bonus_leaves' => ['required', 'numeric', 'min:1'],
            'promo_codes' => ['required', 'string', 'max:255'],
            'points' => ['required', 'numeric', 'min:1', 'max:300'],
        ];
    }

    public function messages()
    {
        return [
            'promo_codes.required' => 'The surprising gift field is required.',
            'promo_codes.string' => 'The surprising gift field must be string.',
            'promo_codes.max' => 'The surprising gift must not be less than 255 characters.'
        ];
    }

    // protected function failedValidation(Validator $validator)
    // {
    //     if ($this->expectsJson()) {
    //         $first_message = collect($validator->errors()->all())->first();
    //         throw new HttpResponseException(response()->json([
    //             'status' => false,
    //             'message' => $first_message
    //         ], 422));
    //     }

    //     // fallback to default behavior (e.g., redirect back for web requests)
    //     parent::failedValidation($validator);
    // }
}
