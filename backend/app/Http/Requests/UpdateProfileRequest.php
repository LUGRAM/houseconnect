<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'     => 'nullable|string|max:100',
            'email'    => [
                'nullable',
                'email',
                'required_without:phone',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'phone'    => [
                'nullable',
                'string',
                'required_without:email',
                Rule::unique('users', 'phone')->ignore($userId),
            ],
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'L\'email ou le téléphone est requis.',
            'phone.required_without' => 'Le téléphone ou l\'email est requis.',
        ];
    }
}
