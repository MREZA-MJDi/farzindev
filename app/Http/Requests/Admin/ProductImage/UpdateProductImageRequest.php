<?php

namespace App\Http\Requests\Admin\ProductImage;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductImageRequest extends FormRequest
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

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'dimensions:min_width=800,min_height=600',
                'max:20480',
            ],

            'alt' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_primary' => [
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sort_order' => $this->sort_order ?? 0,
            'is_primary' => $this->boolean('is_primary'),
        ]);
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'انتخاب محصول الزامی است.',
            'product_id.integer' => 'محصول انتخاب‌شده نامعتبر است.',
            'product_id.exists' => 'محصول انتخاب‌شده وجود ندارد.',

            'image.image' => 'فایل انتخاب‌شده باید تصویر باشد.',
            'image.mimes' => 'فرمت تصویر باید jpg، jpeg، png یا webp باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۲۰ مگابایت باشد.',
            'image.dimensions' => 'ابعاد تصویر باید حداقل ۸۰۰ در ۶۰۰ پیکسل باشد.',

            'alt.string' => 'متن جایگزین تصویر باید متنی باشد.',
            'alt.max' => 'متن جایگزین تصویر نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'sort_order.integer' => 'ترتیب نمایش باید عدد باشد.',
            'sort_order.min' => 'ترتیب نمایش نمی‌تواند منفی باشد.',

            'is_primary.boolean' => 'وضعیت تصویر اصلی نامعتبر است.',
        ];
    }
}
