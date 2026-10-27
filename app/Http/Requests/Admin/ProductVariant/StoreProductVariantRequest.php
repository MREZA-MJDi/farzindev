<?php

namespace App\Http\Requests\Admin\ProductVariant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'type' => [
                'required',
                'string',
                'max:255',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_variants', 'value')
                    ->where('product_id', $this->input('product_id'))
                    ->where('type', $this->input('type')),
            ],

            'color_hex' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^#[0-9A-Fa-f]{3,8}$/',
            ],

            'image_ids' => [
                'nullable',
                'array',
                'max:30',
            ],

            'image_ids.*' => [
                'integer',
                Rule::exists('product_images', 'id')
                    ->where(
                        fn ($query) =>
                            $query->where(
                                'product_id',
                                $this->input('product_id')
                            )
                    ),
            ],

            'media_ids' => [
                'nullable',
                'array',
                'max:30',
            ],

            'media_ids.*' => [
                'required',
                'integer',
                Rule::exists('media', 'id')->where(
                    fn ($query) => $query->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])
                ),
            ],

            'images' => [
                'nullable',
                'array',
                'max:10',
            ],

            'images.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'dimensions:min_width=800,min_height=600',
                'max:20480',
            ],

            'sku' => [
                'nullable',
                'string',
                'max:255',
                'unique:product_variants,sku',
            ],

            'price_adjustment' => [
                'nullable',
                'numeric',
                'decimal:0,2',
            ],

            'stock' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $colorHex = trim((string) ($this->color_hex ?? ''));

        $this->merge([
            'color_hex' => $colorHex !== ''
                ? $colorHex
                : null,

            'price_adjustment' => $this->price_adjustment ?? 0,
            'stock' => $this->stock ?? 0,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'انتخاب محصول الزامی است.',
            'product_id.exists' => 'محصول انتخاب‌شده وجود ندارد.',

            'type.required' => 'نوع ویژگی الزامی است.',
            'type.max' => 'نوع ویژگی نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'name.required' => 'نام ویژگی الزامی است.',
            'name.max' => 'نام ویژگی نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'value.required' => 'مقدار ویژگی الزامی است.',
            'value.max' => 'مقدار ویژگی نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'value.unique' => 'این مقدار ویژگی قبلاً برای همین نوع و محصول ثبت شده است.',

            'color_hex.regex' => 'کد رنگ باید مانند #C8A27A باشد.',

            'image_ids.array' => 'تصاویر انتخاب‌شده نامعتبر هستند.',
            'image_ids.max' => 'حداکثر ۳۰ تصویر می‌توان برای یک تنوع انتخاب کرد.',
            'image_ids.*.exists' => 'یکی از تصاویر انتخاب‌شده متعلق به این محصول نیست.',
            'media_ids.array' => 'فهرست تصاویر کتابخانه رسانه معتبر نیست.',
            'media_ids.max' => 'برای هر تنوع حداکثر ۳۰ تصویر از کتابخانه رسانه انتخاب کنید.',
            'media_ids.*.exists' => 'یکی از تصاویر کتابخانه حذف شده یا فرمت آن پشتیبانی نمی‌شود.',

            'images.array' => 'تصاویر تنوع باید به‌صورت فهرست ارسال شوند.',
            'images.max' => 'برای هر رنگ حداکثر ۱۰ تصویر جدید می‌توانید آپلود کنید.',
            'images.*.image' => 'فایل‌های انتخاب‌شده باید تصویر باشند.',
            'images.*.mimes' => 'تصاویر باید JPG، PNG یا WEBP باشند.',
            'images.*.dimensions' => 'هر تصویر باید حداقل ۸۰۰ در ۶۰۰ پیکسل باشد.',
            'images.*.max' => 'حجم هر تصویر نباید بیشتر از ۲۰ مگابایت باشد.',

            'sku.unique' => 'این SKU قبلاً استفاده شده است.',

            'price_adjustment.numeric' => 'تعدیل قیمت باید عدد باشد.',
            'price_adjustment.decimal' => 'تعدیل قیمت باید حداکثر دو رقم اعشار داشته باشد.',

            'stock.integer' => 'موجودی باید عدد صحیح باشد.',
            'stock.min' => 'موجودی نمی‌تواند منفی باشد.',

            'is_active.boolean' => 'وضعیت فعال/غیرفعال نامعتبر است.',
        ];
    }
}
