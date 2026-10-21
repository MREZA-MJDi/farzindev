<?php

namespace App\Http\Requests\Cart;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:99',
            ],

            /*
             * New flow: one selected variant per product option type.
             * Example: variants[color] = 4, variants[size] = 9.
             */
            'variants' => [
                'nullable',
                'array',
            ],

            'variants.*' => [
                'integer',
                Rule::exists('product_variants', 'id')
                    ->where('is_active', true),
            ],

            /*
             * Kept for backwards compatibility with existing clients/forms.
             */
            'product_variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')
                    ->where('is_active', true),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $product = $this->route('product');

            if (! $product instanceof Product) {
                return;
            }

            if ($product->status !== 'active') {
                $validator->errors()->add(
                    'product',
                    'این محصول در حال حاضر قابل خرید نیست.'
                );

                return;
            }

            if (! $product->category()->where('is_active', true)->exists()) {
                $validator->errors()->add(
                    'product',
                    'دسته‌بندی این محصول در حال حاضر فعال نیست.'
                );

                return;
            }

            $availableVariants = $product->variants()
                ->get()
                ->groupBy('type');

            $submittedVariants = collect(
                $this->input('variants', [])
            )
                ->filter(fn ($id) => filled($id))
                ->mapWithKeys(
                    fn ($id, $type) => [
                        (string) $type => (int) $id,
                    ]
                );

            /*
             * Each configured variant type must have one selected value.
             */
            foreach ($availableVariants as $type => $options) {
                if (! $submittedVariants->has($type)) {
                    $validator->errors()->add(
                        "variants.$type",
                        "انتخاب {$options->first()->name} الزامی است."
                    );
                }
            }

            /*
             * Validate that the submitted option belongs to this product
             * and that its type matches the form key.
             */
            if ($submittedVariants->isNotEmpty()) {
                $variants = $product->variants()
                    ->whereIn(
                        'id',
                        $submittedVariants->values()->all()
                    )
                    ->get()
                    ->keyBy('id');

                if ($variants->count() !== $submittedVariants->count()) {
                    $validator->errors()->add(
                        'variants',
                        'یکی از گزینه‌های انتخاب‌شده معتبر نیست.'
                    );
                }

                foreach ($submittedVariants as $type => $variantId) {
                    $variant = $variants->get($variantId);

                    if (
                        $variant
                        && (string) $variant->type !== (string) $type
                    ) {
                        $validator->errors()->add(
                            "variants.$type",
                            'گزینه انتخاب‌شده با نوع ویژگی سازگار نیست.'
                        );
                    }
                }

                if (
                    $validator->errors()->has('variants') === false
                    && $variants->count() > 0
                    && (int) $variants->min('stock') < 1
                ) {
                    $validator->errors()->add(
                        'variants',
                        'یکی از گزینه‌های انتخاب‌شده موجود نیست.'
                    );
                }
            }

            /*
             * Products without variants still use the product-level stock.
             */
            if (
                $availableVariants->isEmpty()
                && (int) $product->stock < 1
            ) {
                $validator->errors()->add(
                    'product',
                    'این محصول موجود نیست.'
                );
            }

            /*
             * Backwards-compatible single-variant requests.
             */
            if (
                $availableVariants->isEmpty()
                && ! empty($this->input('product_variant_id'))
            ) {
                $validator->errors()->add(
                    'product_variant_id',
                    'این محصول تنوع فعالی برای انتخاب ندارد.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'تعداد محصول را انتخاب کنید.',
            'quantity.integer' => 'تعداد باید عدد صحیح باشد.',
            'quantity.min' => 'تعداد باید حداقل ۱ باشد.',
            'quantity.max' => 'تعداد بیشتر از حد مجاز است.',

            'variants.array' => 'ساختار گزینه‌های محصول نامعتبر است.',
            'variants.*.integer' => 'شناسه تنوع محصول نامعتبر است.',
            'variants.*.exists' => 'یکی از گزینه‌های محصول دیگر در دسترس نیست.',

            'product_variant_id.integer' => 'شناسه تنوع محصول نامعتبر است.',
            'product_variant_id.exists' => 'تنوع انتخاب‌شده دیگر در دسترس نیست.',
        ];
    }
}
