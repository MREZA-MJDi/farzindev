<?php

namespace App\Http\Requests\Admin\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:51200',
                'mimes:jpg,jpeg,png,webp,gif,mp4,webm,pdf,doc,docx,xls,xlsx,zip',
                function ($attribute, $value, $fail): void {
                    if (! $value instanceof UploadedFile) {
                        return;
                    }

                    $extension = strtolower($value->getClientOriginalExtension());
                    $limit = match ($extension) {
                        'gif' => 10 * 1024 * 1024,
                        'mp4', 'webm' => 30 * 1024 * 1024,
                        default => 20 * 1024 * 1024,
                    };

                    if ($value->getSize() > $limit) {
                        $fail(match ($extension) {
                            'gif' => 'حجم GIF نباید بیشتر از ۱۰ مگابایت باشد.',
                            'mp4', 'webm' => 'حجم ویدیو نباید بیشتر از ۳۰ مگابایت باشد.',
                            default => 'حجم فایل نباید بیشتر از ۲۰ مگابایت باشد.',
                        });
                    }
                },
            ],
            'alt' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'انتخاب فایل الزامی است.',
            'file.file' => 'فایل انتخاب‌شده معتبر نیست.',
            'file.max' => 'حجم فایل نباید بیشتر از ۵۰ مگابایت باشد.',
            'file.mimes' => 'فرمت مجاز: JPG، PNG، WEBP، GIF، MP4، WEBM و فایل‌های سند منتخب است؛ SVG و MOV پذیرفته نمی‌شوند.',
            'alt.string' => 'متن جایگزین باید یک متن معتبر باشد.',
            'alt.max' => 'متن جایگزین نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'description.string' => 'توضیحات باید به صورت متن وارد شود.',
            'description.max' => 'توضیحات نمی‌تواند بیشتر از ۲۰۰۰ کاراکتر باشد.',
        ];
    }
}
