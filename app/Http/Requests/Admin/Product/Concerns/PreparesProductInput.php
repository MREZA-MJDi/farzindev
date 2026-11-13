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
            'specifications' => $this->normalizeSpecifications($this->input('specifications')),
            'faqs' => $this->normalizeFaqs($this->input('faqs')),
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
     * Keep specifications as an ordered list of free-text label/value pairs.
     * Legacy associative JSON values are converted without discarding their content.
     */
    private function normalizeSpecifications(mixed $specifications): ?array
    {
        if (! is_array($specifications)) {
            return null;
        }

        $rows = [];
        foreach ($specifications as $key => $specification) {
            if (is_array($specification)) {
                $label = trim((string) ($specification['label'] ?? ''));
                $value = trim((string) ($specification['value'] ?? ''));
            } else {
                $label = (string) $key;
                $value = is_scalar($specification) ? trim((string) $specification) : '';
            }

            if ($value === '') {
                continue;
            }

            $rows[] = ['label' => $label, 'value' => $value];
        }

        return $rows;
    }

    /**
     * Keep only completed FAQ entries and trim user-managed plain text.
     *
     * @return array<int, array{question: string, answer: string}>
     */
    private function normalizeFaqs(mixed $faqs): array
    {
        if (! is_array($faqs)) {
            return [];
        }

        return collect($faqs)
            ->filter(fn ($faq): bool => is_array($faq)
                && (filled($faq['question'] ?? null) || filled($faq['answer'] ?? null)))
            ->map(fn (array $faq): array => [
                'question' => trim((string) ($faq['question'] ?? '')),
                'answer' => trim((string) ($faq['answer'] ?? '')),
            ])
            ->values()
            ->all();
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
