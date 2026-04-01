<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GalleryImagesRequest extends FormRequest
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
            'gallery_images' => 'required|array',
            'gallery_images.*' => 'required|mimes:jpg,jpeg,png,tiff,webp',
        ];
    }

    public function messages(): array
    {
        return [
            'gallery_images.required' => 'Please upload at least one image.',
            'gallery_images.array' => 'The uploaded images must be in an array format.',
            'gallery_images.*.mimes' => 'Each image must be of type: jpg, jpeg, png, tiff, or webp.',
        ];
    }
}
