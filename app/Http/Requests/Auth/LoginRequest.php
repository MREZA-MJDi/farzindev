<?php

namespace App\Http\Requests\Auth;

use App\Support\IranianMobile;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $identifier = (string) $this->input('phone', '');
        $identifierRule = str_contains($identifier, '@')
            ? 'email:rfc'
            : 'regex:/^09\d{9}$/';

        return [
            'phone' => [
                'required',
                'string',
                'max:255',
                $identifierRule,
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],
            'remember' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'شماره موبایل را وارد کنید.',
            'phone.email' => 'برای ورود با ایمیل مدیر، یک ایمیل معتبر وارد کنید.',
            'phone.regex' => 'شماره موبایل باید مانند 09123456789 باشد.',
            'password.required' => 'رمز عبور را وارد کنید.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => 'شماره موبایل',
            'password' => 'رمز عبور',
            'remember' => 'مرا به خاطر بسپار',
        ];
    }

    protected function prepareForValidation(): void
    {
        $identifier = trim((string) $this->input('phone', ''));

        $this->merge([
            'phone' => str_contains($identifier, '@')
                ? mb_strtolower($identifier)
                : IranianMobile::normalize($identifier),
            'remember' => $this->boolean('remember'),
        ]);
    }
}
