<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ContactSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'latitude' => [
                'nullable',
                'required_with:longitude',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'required_with:latitude',
                'numeric',
                'between:-180,180',
            ],

            'hours' => [
                'nullable',
                'string',
            ],

            'phone_label' => [
                'required',
                'string',
                'max:100',
            ],

            'email_label' => [
                'required',
                'string',
                'max:100',
            ],

            'support_label' => [
                'required',
                'string',
                'max:100',
            ],

            'online_label' => [
                'required',
                'string',
                'max:100',
            ],

            'support_description' => [
                'nullable',
                'string',
            ],

            'online_description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => 'شماره تماس',
            'email' => 'ایمیل',
            'address' => 'آدرس',
            'latitude' => 'عرض جغرافیایی (Latitude)',
            'longitude' => 'طول جغرافیایی (Longitude)',
            'hours' => 'ساعات کاری',
            'phone_label' => 'عنوان تماس تلفنی',
            'email_label' => 'عنوان ایمیل',
            'support_label' => 'عنوان پشتیبانی',
            'online_label' => 'عنوان ارتباط آنلاین',
            'support_description' => 'توضیحات پشتیبانی',
            'online_description' => 'توضیحات ارتباط آنلاین',
            'is_active' => 'وضعیت فعال بودن',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'فرمت ایمیل واردشده صحیح نیست.',
            'latitude.required_with' => 'برای ثبت مختصات، هر دو مقدار Latitude و Longitude را وارد کنید.',
            'longitude.required_with' => 'برای ثبت مختصات، هر دو مقدار Latitude و Longitude را وارد کنید.',
            'latitude.between' => 'عرض جغرافیایی باید بین ۹۰- و ۹۰ باشد.',
            'longitude.between' => 'طول جغرافیایی باید بین ۱۸۰- و ۱۸۰ باشد.',

            'phone.max' => 'شماره تماس نمی‌تواند بیشتر از :max کاراکتر باشد.',

            'phone_label.required' => 'عنوان تماس تلفنی الزامی است.',
            'email_label.required' => 'عنوان ایمیل الزامی است.',
            'support_label.required' => 'عنوان پشتیبانی الزامی است.',
            'online_label.required' => 'عنوان ارتباط آنلاین الزامی است.',

            '*.string' => 'مقدار واردشده باید به‌صورت متن باشد.',
        ];
    }
}
