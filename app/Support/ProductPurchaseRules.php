<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Shared storefront rules for purchase availability and installment eligibility.
 *
 * The active variant relation is grouped by option type: every required type
 * must have at least one purchasable option. A single available option in one
 * group must never make a product appear purchasable when another group is
 * completely out of stock.
 */
final class ProductPurchaseRules
{
    /**
     * @param Collection<int, \App\Models\ProductVariant>|null $variants
     */
    public static function isAvailable(Product $product, ?Collection $variants = null): bool
    {
        $variants ??= self::variants($product);

        if ($variants->isEmpty()) {
            return (int) $product->stock > 0;
        }

        $groups = $variants->groupBy(
            static fn ($variant): string => trim((string) $variant->type) ?: '__missing_type__'
        );

        return $groups->isNotEmpty()
            && $groups->every(
                static fn (Collection $options): bool => $options->contains(
                    static fn ($option): bool => (int) $option->stock > 0
                )
            );
    }

    /**
     * Installment badge visibility is based on the same valid plan rules as
     * product detail. Variable-price variants are excluded because the saved
     * installment plan is calculated from the base product price.
     *
     * @param Collection<int, \App\Models\ProductVariant>|null $variants
     */
    public static function hasValidInstallmentPlan(Product $product, ?Collection $variants = null): bool
    {
        $variants ??= self::variants($product);

        if (
            ! $product->installment_enabled
            || ! self::isAvailable($product, $variants)
            || (int) $product->installment_cash_percent < 1
            || (int) $product->installment_cash_percent > 99
            || $product->installment_remainder_method !== 'cheque'
            || (int) $product->installment_cheque_count < 1
            || (int) $product->installment_cheque_count > 36
            || (int) $product->installment_interval_months < 1
            || (int) $product->installment_interval_months > 24
            || (float) $product->price <= 0
        ) {
            return false;
        }

        return $variants->every(
            static fn ($variant): bool => abs((float) $variant->price_adjustment) < 0.01
        );
    }

    /**
     * @return Collection<int, \App\Models\ProductVariant>
     */
    private static function variants(Product $product): Collection
    {
        return $product->relationLoaded('variants')
            ? $product->getRelation('variants')
            : $product->variants()->get();
    }
}
