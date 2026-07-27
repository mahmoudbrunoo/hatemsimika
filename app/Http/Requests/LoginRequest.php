<?php

namespace App\Http\Requests;

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
            'login' => ['required', 'string'], // بريد إلكتروني أو رقم موبايل
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'login' => 'البريد الإلكتروني أو رقم الموبايل',
            'password' => 'كلمة المرور',
        ];
    }
}
