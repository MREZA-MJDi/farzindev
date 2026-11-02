<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'subject' => [
                'required',
                'string',
                'in:product,installment,order,shipping,other',
            ],

            'product_model' => ['nullable', 'string', 'max:120'],
            'width_cm' => ['nullable', 'integer', 'min:1', 'max:500'],
            'callback_requested' => ['nullable', 'boolean'],
            'photo' => [
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:10240',
            ],

            'message' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'وارد کردن نام و نام خانوادگی الزامی است.',
            'name.max' => 'نام نمی‌تواند بیشتر از ۱۲۰ کاراکتر باشد.',

            'phone.required' => 'وارد کردن شماره تماس الزامی است.',
            'phone.max' => 'شماره تماس واردشده معتبر نیست.',

            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'email.max' => 'ایمیل نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

            'subject.required' => 'لطفاً موضوع پیام را انتخاب کنید.',
            'subject.in' => 'موضوع انتخاب‌شده معتبر نیست.',
            'product_model.max' => 'نام یا مدل محصول نباید بیشتر از ۱۲۰ کاراکتر باشد.',
            'width_cm.integer' => 'عرض باید به‌صورت عدد وارد شود.',
            'width_cm.min' => 'عرض واردشده معتبر نیست.',
            'width_cm.max' => 'عرض واردشده از محدوده مجاز بیشتر است.',
            'photo.image' => 'فایل پیوست باید تصویر معتبر باشد.',
            'photo.mimes' => 'فرمت تصویر باید JPG، PNG یا WEBP باشد.',
            'photo.max' => 'حجم تصویر نباید بیشتر از ۱۰ مگابایت باشد.',

            'message.required' => 'نوشتن پیام الزامی است.',
            'message.min' => 'پیام باید حداقل ۱۰ کاراکتر باشد.',
            'message.max' => 'پیام نمی‌تواند بیشتر از ۵۰۰۰ کاراکتر باشد.',
        ];
    }
}
