<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return !$this->user();
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact_number' => ['required', 'regex:/^09\d{9}$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'data_privacy_consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered.',
            'contact_number.required' => 'Enter your Philippine mobile number.',
            'contact_number.regex' => 'Enter a valid Philippine mobile number starting with 09 (11 digits).',
            'data_privacy_consent.accepted' => 'You must agree to the data privacy notice to continue.',
        ];
    }
}
