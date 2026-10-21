<?php

namespace App\Http\Requests\Admin\Category;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->slug
                ? Str::slug($this->slug)
                : Str::slug($this->name),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->sort_order ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:categories,slug',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:20480',
            ],

            'hover_media_id' => [
                'nullable',
                'integer',
                'exists:media,id',
                function ($attribute, $value, $fail): void {
                    if ($value === null || $value === '') return;
                    $media = Media::query()->find($value);
                    if (! $media || $media->mime_type !== 'image/gif' || $media->size > 10 * 1024 * 1024) {
                        $fail('فقط GIF موجود در کتابخانه رسانه با حجم حداکثر ۱۰ مگابایت قابل انتخاب است.');
                    }
                },
            ],

            'is_active' => [
                'boolean',
            ],

            'sort_order' => [
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام دسته‌بندی الزامی است.',
            'name.max' => 'نام دسته‌بندی نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'slug.required' => 'Slug دسته‌بندی الزامی است.',
            'slug.unique' => 'این Slug قبلاً استفاده شده است.',

            'description.string' => 'توضیحات باید به صورت متن باشد.',

            'image.image' => 'فایل انتخاب‌شده باید تصویر باشد.',
            'image.mimes' => 'فرمت تصویر باید jpg، jpeg، png یا webp باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۲۰ مگابایت باشد.',
            'hover_media_id.integer' => 'رسانه انتخاب‌شده معتبر نیست.',
            'hover_media_id.exists' => 'GIF انتخاب‌شده در کتابخانه رسانه پیدا نشد.',

            'sort_order.integer' => 'ترتیب نمایش باید عدد باشد.',
            'sort_order.min' => 'ترتیب نمایش نمی‌تواند منفی باشد.',
        ];
    }
}
