<?php

namespace App\\Http\\Controllers;

use App\\Models\\Product;
use App\\Support\\ProductPurchaseRules;
use Illuminate\\Contracts\\View\\View;
use Illuminate\\Database\\Eloquent\\Builder;

class AboutController extends Controller
{
    public function index(): View
    {
        $installmentProduct = Product::query()
            ->active()
            ->where('price', '>', 0)
            ->where('installment_enabled', true)
            ->whereBetween('installment_cash_percent', [1, 99])
            ->where('installment_remainder_method', 'cheque')
            ->whereBetween('installment_cheque_count', [1, 36])
            ->whereBetween('installment_interval_months', [1, 24])
            ->whereHas('category', static fn (Builder $category): Builder => $category->where('is_active', true))
            ->whereHas('images')
            ->where(function (Builder $sku): void {
                $sku->whereNull('sku')->orWhere(function (Builder $realSku): void {
                    $realSku
                        ->where('sku', 'not like', 'FZ-SEED-%')
                        ->where('sku', 'not like', 'LIV-%')
                        ->where('sku', 'not like', 'E2E-%');
                });
            })
            ->with('variants')
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get()
            ->first(static fn (Product $product): bool =>
                ProductPurchaseRules::hasValidInstallmentPlan($product, $product->variants)
            );

        return view('about.index', [
            'aboutInstallmentProduct' => $installmentProduct,
        ]);
    }
}
