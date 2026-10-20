<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $installmentEnabled =
            $this->boolean('installment_enabled');

        $this->merge([
            'price' => $this->normalizeNumericInput($this->input('price')),
            'compare_at_price' => $this->normalizeNumericInput($this->input('compare_at_price')),
            'slug' =>
                $this->slug
                    ?: Str::slug($this->name),

            'stock' =>
                $this->normalizeNumericInput($this->input('stock')) ?? 0,

            'is_featured' =>
                $this->boolean('is_featured'),

            'is_new' =>
                $this->boolean('is_new'),

            'installment_enabled' =>
                $installmentEnabled,

            /*
             * Disable installment settings completely
             * when installment sales are disabled.
             */
            'installment_cash_percent' =>
                $installmentEnabled
                    ? ($this->installment_cash_percent ?? 50)
                    : null,

            'installment_remainder_method' =>
                $installmentEnabled
                    ? ($this->installment_remainder_method ?? 'cheque')
                    : null,

            'installment_cheque_count' =>
                $installmentEnabled
                    ? ($this->installment_cheque_count ?? 2)
                    : null,

            'installment_interval_months' =>
                $installmentEnabled
                    ? ($this->installment_interval_months ?? 2)
                    : null,
        ]);
    }

    /**
     * Normalize Persian/Arabic digits and formatted money values
     * before Laravel numeric validation runs.
     */
    private function normalizeNumericInput(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];

        $value = str_replace(
            $persian,
            ['0','1','2','3','4','5','6','7','8','9'],
            $value
        );

        $value = str_replace(
            $arabic,
            ['0','1','2','3','4','5','6','7','8','9'],
            $value
        );

        $value = str_replace(
            [',', '٬', ' ', '٫'],
            ['', '', '', '.'],
            $value
        );

        $value = preg_replace('/[^0-9.\-]/u', '', $value) ?? '';

        if (substr_count($value, '.') > 1) {
            $parts = explode('.', $value);
            $value = array_shift($parts) . '.' . implode('', $parts);
        }

        return $value !== '' ? $value : null;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:products,slug',
            ],

            'sku' => [
                'required',
                'string',
                'max:255',
                'unique:products,sku',
            ],

            'short_description' => [
                'nullable',
                'string',
                'max:255',
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

            'price' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999999.99',
                'decimal:0,2',
            ],

            'compare_at_price' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999999.99',
                'decimal:0,2',
                'gte:price',
            ],

            'stock' => [
                'integer',
                'min:0',
            ],

            'status' => [
                'required',
                Rule::in([
                    'draft',
                    'active',
                    'archived',
                ]),
            ],

            'is_featured' => [
                'boolean',
            ],

            'is_new' => [
                'boolean',
            ],

            /*
             |--------------------------------------------------------------------------
             | Installments
             |--------------------------------------------------------------------------
             */

            'installment_enabled' => [
                'boolean',
            ],

            'installment_cash_percent' => [
                'nullable',
                'integer',
                'between:1,99',
                'required_if:installment_enabled,1',
            ],

            'installment_remainder_method' => [
                'nullable',
                'string',
                Rule::in([
                    'cheque',
                ]),
                'required_if:installment_enabled,1',
            ],

            'installment_cheque_count' => [
                'nullable',
                'integer',
                'min:1',
                'max:30',
                'required_if:installment_enabled,1',
            ],

            'installment_interval_months' => [
                'nullable',
                'integer',
                'min:1',
                'max:24',
                'required_if:installment_enabled,1',
            ],

            /*
             |--------------------------------------------------------------------------
             | SEO
             |--------------------------------------------------------------------------
             */

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'انتخاب دسته‌بندی الزامی است.',
            'category_id.integer' => 'دسته‌بندی انتخاب‌شده نامعتبر است.',
            'category_id.exists' => 'دسته‌بندی انتخاب‌شده وجود ندارد.',

            'name.required' => 'نام محصول الزامی است.',
            'name.string' => 'نام محصول باید متنی باشد.',
            'name.max' => 'نام محصول نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'slug.required' => 'Slug محصول الزامی است.',
            'slug.string' => 'Slug محصول باید متنی باشد.',
            'slug.max' => 'Slug محصول نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'slug.unique' => 'این Slug قبلاً استفاده شده است.',

            'sku.required' => 'SKU محصول الزامی است.',
            'sku.string' => 'SKU محصول باید متنی باشد.',
            'sku.max' => 'SKU نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'sku.unique' => 'این SKU قبلاً استفاده شده است.',

            'short_description.max' => 'توضیح کوتاه نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'image.image' => 'فایل انتخاب‌شده باید تصویر باشد.',
            'image.mimes' => 'فرمت تصویر باید jpg، jpeg، png یا webp باشد.',
            'image.max' => 'حجم تصویر نباید بیشتر از ۲۰ مگابایت باشد.',

            'price.required' => 'وارد کردن قیمت محصول الزامی است.',
            'price.numeric' => 'قیمت محصول باید یک عدد معتبر باشد.',
            'price.min' => 'قیمت محصول نمی‌تواند منفی باشد.',
            'price.max' => 'قیمت محصول بیش از سقف مجاز است.',
            'price.decimal' => 'قیمت محصول باید حداکثر دو رقم اعشار داشته باشد.',

            'compare_at_price.numeric' => 'قیمت قبل باید یک عدد معتبر باشد.',
            'compare_at_price.min' => 'قیمت قبل نمی‌تواند منفی باشد.',
            'compare_at_price.max' => 'قیمت قبل بیش از سقف مجاز است.',
            'compare_at_price.decimal' => 'قیمت قبل باید حداکثر دو رقم اعشار داشته باشد.',
            'compare_at_price.gte' => 'قیمت قبل باید بیشتر یا مساوی قیمت فعلی باشد.',

            'stock.integer' => 'موجودی باید عدد صحیح باشد.',
            'stock.min' => 'موجودی نمی‌تواند منفی باشد.',

            'status.required' => 'وضعیت محصول را انتخاب کنید.',
            'status.in' => 'وضعیت محصول نامعتبر است.',

            'is_featured.boolean' => 'وضعیت محصول ویژه نامعتبر است.',
            'is_new.boolean' => 'وضعیت محصول جدید نامعتبر است.',

            'installment_enabled.boolean' => 'وضعیت خرید اقساطی نامعتبر است.',
            'installment_cash_percent.integer' => 'درصد پیش‌پرداخت باید عدد صحیح باشد.',
            'installment_cash_percent.between' => 'درصد پیش‌پرداخت باید بین ۱ تا ۹۹ باشد.',
            'installment_cash_percent.required_if' => 'درصد پیش‌پرداخت الزامی است.',
            'installment_remainder_method.in' => 'روش تسویه انتخاب‌شده نامعتبر است.',
            'installment_remainder_method.required_if' => 'روش تسویه باقی‌مانده الزامی است.',
            'installment_cheque_count.integer' => 'تعداد چک باید عدد صحیح باشد.',
            'installment_cheque_count.min' => 'تعداد چک باید حداقل ۱ باشد.',
            'installment_cheque_count.max' => 'تعداد چک نمی‌تواند بیشتر از ۳۰ باشد.',
            'installment_cheque_count.required_if' => 'تعداد چک الزامی است.',
            'installment_interval_months.integer' => 'فاصله سررسید باید عدد صحیح باشد.',
            'installment_interval_months.min' => 'فاصله سررسید باید حداقل ۱ ماه باشد.',
            'installment_interval_months.max' => 'فاصله سررسید نمی‌تواند بیشتر از ۲۴ ماه باشد.',
            'installment_interval_months.required_if' => 'فاصله سررسید الزامی است.',

            'meta_title.max' => 'عنوان SEO نباید بیشتر از ۲۵۵ کاراکتر باشد.',
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'دسته‌بندی',
            'name' => 'نام محصول',
            'slug' => 'Slug',
            'sku' => 'SKU',
            'short_description' => 'توضیح کوتاه',
            'description' => 'توضیحات',
            'image' => 'تصویر محصول',
            'price' => 'قیمت',
            'compare_at_price' => 'قیمت قبل',
            'stock' => 'موجودی',
            'status' => 'وضعیت',
            'installment_enabled' => 'خرید اقساطی',
            'installment_cash_percent' => 'درصد پیش‌پرداخت',
            'installment_remainder_method' => 'روش تسویه',
            'installment_cheque_count' => 'تعداد چک',
            'installment_interval_months' => 'فاصله سررسید',
            'meta_title' => 'عنوان SEO',
            'meta_description' => 'توضیحات SEO',
        ];
    }}
