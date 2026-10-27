<?php

namespace App\Http\Requests\Admin\Product\Concerns;

use Illuminate\Support\Str;

/**
 * Shared input normalization for creating and updating catalog products.
 */
trait PreparesProductInput
{
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

            'show_in_home_gallery' =>
                $this->boolean('show_in_home_gallery'),

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

}
