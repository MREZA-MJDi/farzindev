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

                    // Determine upload limits from the detected MIME type, not
                    // the user-controlled original filename extension.
                    $mime = strtolower((string) $value->getMimeType());
                    $isImage = str_starts_with($mime, 'image/');
                    $isVideo = str_starts_with($mime, 'video/');
                    $limit = $isImage
                        ? 10 * 1024 * 1024
                        : ($isVideo ? 30 * 1024 * 1024 : 20 * 1024 * 1024);

                    if ($value->getSize() > $limit) {
                        $fail($isImage
                            ? 'حجم تصویر نباید بیشتر از ۱۰ مگابایت باشد.'
                            : ($isVideo
                                ? 'حجم ویدیو نباید بیشتر از ۳۰ مگابایت باشد.'
                                : 'حجم فایل نباید بیشتر از ۲۰ مگابایت باشد.'));
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
