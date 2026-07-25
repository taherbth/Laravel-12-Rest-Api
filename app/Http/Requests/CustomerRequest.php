<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Factory;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function messages()
    {
        return [
            // Customer Number
            'customer_no.required' => 'The customer number is required.',
            'customer_no.unique'   => 'This customer number is already registered.',

            // Email
            'email.email'          => 'Please enter a valid email address.',
            'email.unique'         => 'This email address is already taken.',

            // Cell Phone
            'cell_phone.regex'     => 'The cell phone format is invalid. It must start with 01 and be followed by 9 digits.',
            'cell_phone.unique'    => 'This cell phone number is already registered.',

            // Name and Gender
            'first_name.required'  => 'First name is required.',
            'first_name.min'       => 'First name must be at least 2 characters.',
            'first_name.max'       => 'First name may not exceed 100 characters.',
            'last_name.required'   => 'Last name is required.',
            'last_name.min'        => 'Last name must be at least 2 characters.',
            'last_name.max'        => 'Last name may not exceed 100 characters.',
            'gender_id.required'   => 'Please select a gender.',

            // Address Details
            'address.required'     => 'The primary address field is required.',
            'city.required'        => 'City is required.',
            'country_id.required'  => 'Please select a country.',
            'date_of_birth.date'   => 'Please provide a valid date.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array 
    {
        switch ( $this->method() ){
            case 'POST':
                $rules = [];
                $rules = array_merge($rules,['customer_no' => ['required', Rule::unique('customers')->where(function ($query) {
                            $query->where('deleted_at', Null);
                        })
                    ]
                ]);
                if($this->input('email')!=""){
                    $rules = array_merge($rules,['email' => ['email', Rule::unique('customers')->where(function ($query) {
                                $query->where('deleted_at', Null);
                            })
                        ]
                    ]);
                }
                if($this->input('cell_phone')!=""){
                    $rules = array_merge($rules,['cell_phone' => ['regex:/(01)[0-9]{9}$/', Rule::unique('customers')->where(function ($query) {
                                $query->where('deleted_at', Null);
                            })
                        ]
                    ]);
                }             
                $rules = array_merge($rules,['first_name' => 'required|string|min:2|max:100']);
                $rules = array_merge($rules,['last_name' => 'required|string|min:2|max:100']);
                $rules = array_merge($rules,['gender_id' => 'required']);
                $rules = array_merge($rules,['address' => 'required']);
                $rules = array_merge($rules,['address2' => 'nullable|string']);
                $rules = array_merge($rules,['city' => 'required']);
                $rules = array_merge($rules,['country_id' => 'required']);
                $rules = array_merge($rules,['zip' => 'nullable|string']);
                $rules = array_merge($rules,['work_phone' => 'nullable|string']);
                $rules = array_merge($rules,['date_of_birth' => 'nullable|date']);
                return $rules;
                break;

            case 'PUT':
                $rules = [];
                $rules = array_merge($rules,['customer_no' => ['required', Rule::unique('customers')->where(function ($query) {
                            $query->where('deleted_at', null);
                            $query->where('id', '!=', $this->get('id'));
                        })
                    ]
                ]);
                if($this->input('email')!=""){
                    $rules = array_merge($rules,['email' => ['email', Rule::unique('customers')->where(function ($query) {
                                $query->where('deleted_at', Null);
                                $query->where('id', '!=', $this->get('id'));
                            })
                        ]
                    ]);
                }
                if($this->input('cell_phone')!=""){
                    $rules = array_merge($rules,['cell_phone' => ['regex:/(01)[0-9]{9}$/', Rule::unique('customers')->where(function ($query) {
                                $query->where('deleted_at', Null);
                                $query->where('id', '!=', $this->get('id'));
                            })
                        ]
                    ]);
                }             
                $rules = array_merge($rules,['first_name' => 'required|string|min:2|max:100']);
                $rules = array_merge($rules,['last_name' => 'required|string|min:2|max:100']);
                $rules = array_merge($rules,['gender_id' => 'required']);
                $rules = array_merge($rules,['address' => 'required']);
                $rules = array_merge($rules,['city' => 'required']);
                $rules = array_merge($rules,['country_id' => 'required']);
                return $rules;
                break;               
        }
    }
}
