<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class GalleryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Optional: Custom error messages for specific validation rules.
     */
    public function messages(): array
    {
        return [
            'cover_photo.required' => 'Please upload a cover photo.',
            'cover_photo.max' => 'The cover photo may not be greater than 5MB.',
            'files.*.max' => 'Each attached file must not exceed 10MB.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string','min:3', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_photo' => ['required', 'image', 'max:5120'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:10240'],
        ];
    }
}
