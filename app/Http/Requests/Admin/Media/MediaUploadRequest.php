<?php

namespace App\Http\Requests\Admin\Media;

use Illuminate\Foundation\Http\FormRequest;

class MediaUploadRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:20480',
                'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,xls,xlsx,zip',
                function ($attribute, $value, $fail): void {
                    if ($value instanceof \\Illuminate\\Http\\UploadedFile
                        && strtolower($value->getClientOriginalExtension()) === 'gif'
                        && $value->getSize() > 10 * 1024 * 1024) {
                        $fail('حجم GIF نباید بیشتر از ۱۰ مگابایت باشد.');
                    }
                },
            ],

            'alt' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'انتخاب فایل الزامی است.',
            'file.file' => 'فایل انتخاب‌شده معتبر نیست.',
            'file.max' => 'حجم فایل نباید بیشتر از ۲۰ مگابایت باشد.',
            'file.mimes' => 'فرمت فایل مجاز نیست؛ SVG در کتابخانه رسانه پذیرفته نمی‌شود.',

            'alt.string' => 'متن جایگزین باید یک متن معتبر باشد.',
            'alt.max' => 'متن جایگزین نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',

            'description.string' => 'توضیحات باید به صورت متن وارد شود.',
            'description.max' => 'توضیحات نمی‌تواند بیشتر از ۲۰۰۰ کاراکتر باشد.',
        ];
    }
}
