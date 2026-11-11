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
        $requestedSlug = trim((string) $this->input('slug', ''));
        $slug = Str::slug($requestedSlug !== '' ? $requestedSlug : (string) $this->input('name', ''));

        // Persian titles may not transliterate on every production image.
        // Always produce a stable ASCII URL instead of failing category creation.
        if ($slug === '') {
            $slug = 'category-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(6));
        }

        $this->merge([
            'slug' => $slug,
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

            'description' => ['nullable', 'string', 'max:1200'],
            'content_title' => ['nullable', 'string', 'max:180'],
            'content_body' => ['nullable', 'string', 'max:12000],
            'content_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'dimensions:min_width=800,min_height=450', 'max:20480'],

            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url:https', 'max:2048'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'seo_robots' => ['nullable', Rule::in(['inherit', 'index,follow', 'noindex,follow', 'noindex,nofollow'])],

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
                    if ($value === null || $value === '') {
                        return;
                    }

                    $media = Media::query()->find($value);
                    $validGif = $media
                        && $media->mime_type === 'image/gif'
                        && $media->size <= 10 * 1024 * 1024;
                    $validVideo = $media
                        && filled($media->url)
                        && in_array($media->mime_type, ['video/mp4', 'video/webm'], true)
                        && $media->size <= 30 * 1024 * 1024;

                    if (! $validGif && ! $validVideo) {
                        $fail('فقط GIF حداکثر ۱۰ مگابایت یا ویدیوی MP4/WEBM حداکثر ۳۰ مگابایت از کتابخانه رسانه قابل انتخاب است.');
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
            'content_title.max' => 'عنوان راهنمای دسته‌بندی حداکثر ۱۸۰ کاراکتر است.',
            'content_body.max' => 'متن راهنمای دسته‌بندی بیش از حد طولانی است.',
            'content_image.image' => 'تصویر راهنمای دسته‌بندی معتبر نیست.',
            'content_image.dimensions' => 'تصویر راهنما باید حداقل ۸۰۰ در ۴۵۰ پیکسل باشد.',
            'content_image.max' => 'حجم تصویر راهنما نباید بیشتر از ۲۰ مگابایت باشد.',
            'meta_title.max' => 'عنوان SEO حداکثر ۲۵۵ کاراکتر است.',
            'meta_description.max' => 'توضیحات SEO حداکثر ۳۲۰ کاراکتر است.',

            'image.image' => 'فایل انتخاب‌شده باید تصویر باشد.',
            'image.mimes' => 'فرمت تصویر باید jpg، jpeg، png یا webp باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۲۰ مگابایت باشد.',
            'hover_media_id.integer' => 'رسانه انتخاب‌شده معتبر نیست.',
            'hover_media_id.exists' => 'رسانه انتخاب‌شده در کتابخانه رسانه پیدا نشد.',

            'sort_order.integer' => 'ترتیب نمایش باید عدد باشد.',
            'sort_order.min' => 'ترتیب نمایش نمی‌تواند منفی باشد.',
        ];
    }
}
