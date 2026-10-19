<?php

namespace App\Http\Requests\Auth;

use App\Support\IranianMobile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $email = trim((string) $this->input('email', ''));

        $this->merge([
            'email' => $email !== '' ? mb_strtolower($email) : null,
            'phone' => IranianMobile::normalize($this->input('phone')),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^09\d{9}$/',
                Rule::unique('users', 'phone'),
            ],
            'email' => [
                'nullable',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers(),
            ],
            'terms' => [
                'accepted',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'شماره موبایل برای ساخت حساب الزامی است.',
            'phone.regex' => 'شماره موبایل باید با فرمت 09123456789 وارد شود.',
            'phone.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'password.letters' => 'رمز عبور باید حداقل یک حرف داشته باشد.',
            'password.mixed' => 'رمز عبور باید حداقل یک حرف بزرگ و یک حرف کوچک داشته باشد.',
            'password.numbers' => 'رمز عبور باید حداقل یک عدد داشته باشد.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور یکسان نیست.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'terms.accepted' => 'برای ایجاد حساب، پذیرش قوانین و شرایط الزامی است.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'phone' => 'شماره موبایل',
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
            'terms' => 'قوانین و شرایط',
        ];
    }
}
