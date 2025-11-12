<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => 'nullable|email|required_without:phone',
            'phone'    => 'nullable|string|required_without:email',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'L\'email ou le téléphone est requis.',
            'phone.required_without' => 'Le téléphone ou l\'email est requis.',
            'password.required' => 'Le mot de passe est requis.',
        ];
    }
}
