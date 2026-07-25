<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
     * Using sometimes tells Laravel: "Only validate this field if it is present in the request."
     */
   
    public function rules() : array
    {
        // 1. Define rules common to both creating and updating
        $rules = [
            'description' => 'sometimes|nullable|string',
        ];

        // 2. Handle POST (Create)
        if ($this->isMethod('post')) {
            return array_merge($rules, [
                'name'  => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
            ]);
        }

        // 3. Handle PUT/PATCH (Update)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return array_merge($rules, [
                'name' => [
                    // Only validates if 'name' is in the request
                    'sometimes','required','min:3',
                    Rule::unique('products')->ignore($this->route('product'))->whereNull('deleted_at')
                ],
                'price' => 'sometimes|required|numeric|min:0',
            ]);
        }
        return $rules;
    }
}

