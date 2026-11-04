<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

final class ProductPurchaseRules
{
    /**
     * No variants: parent stock is authoritative. Variant-backed products
     * need at least one in-stock option in every configured option type.
     *
     * @param Collection<int, \App\Models\ProductVariant>|null $variants
     */
    public static function isAvailable(Product $product, ?Collection $variants = null): bool
    {
        $variants ??= $product->relationLoaded('variants')
            ? $product->getRelation('variants')
            : $product->variants()->get();

        if ($variants->isEmpty()) {
            return (int) $product->stock > 0;
        }

        $groups = $variants->groupBy(
            static fn ($variant): string => trim((string) $variant->type) ?: '__missing_type__'
        );

        return $groups->isNotEmpty()
            && $groups->every(static fn (Collection $options): bool => $options->contains(
                static fn ($option): bool => (int) $option->stock > 0
            ));
    }

    /**
     * SQL counterpart keeps the same availability rule before pagination.
     */
    public static function applyAvailableQuery(Builder $query): Builder
    {
        return $query->where(function (Builder $availability): void {
            $availability
                ->where(function (Builder $simple): void {
                    $simple->whereDoesntHave('variants')->where('products.stock', '>', 0);
                })
                ->orWhere(function (Builder $configured): void {
                    $configured
                        ->whereHas('variants')
                        ->whereNotExists(function (QueryBuilder $unavailableType): void {
                            $unavailableType
                                ->selectRaw('1')
                                ->from('product_variants as required_variants')
                                ->whereColumn('required_variants.product_id', 'products.id')
                                ->where('required_variants.is_active', true)
                                ->groupBy('required_variants.type')
                                ->havingRaw('SUM(CASE WHEN required_variants.stock > 0 THEN 1 ELSE 0 END) = 0');
                        });
                });
        });
    }

    /**
     * A plan is visible only for an available product with a fully valid
     * cheque schedule and fixed-price variants.
     *
     * @param Collection<int, \App\Models\ProductVariant>|null $variants
     */
    public static function hasValidInstallmentPlan(Product $product, ?Collection $variants = null): bool
    {
        $variants ??= $product->relationLoaded('variants')
            ? $product->getRelation('variants')
            : $product->variants()->get();

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

    public static function applyInstallmentQuery(Builder $query): Builder
    {
        self::applyAvailableQuery($query);

        return $query
            ->where('products.installment_enabled', true)
            ->whereBetween('products.installment_cash_percent', [1, 99])
            ->where('products.installment_remainder_method', 'cheque')
            ->whereBetween('products.installment_cheque_count', [1, 36])
            ->whereBetween('products.installment_interval_months', [1, 24])
            ->where('products.price', '>', 0)
            ->whereDoesntHave('variants', static fn (Builder $variants): Builder => $variants
                ->whereRaw('ABS(COALESCE(price_adjustment, 0)) >= 0.01'));
    }
}
