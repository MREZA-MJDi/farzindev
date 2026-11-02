<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\Shop\ShopFilterRequest;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->whereHas('products', fn ($query) => $query->active()->publicCatalogue()->availableForPurchase())
            ->with([
                'hoverMedia',
                'latestActiveProduct.images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'latestActiveProduct.images.media:id,path',
            ])
            ->withCount([
                'products' => fn ($query) => $query->active()->publicCatalogue()->availableForPurchase(),
            ])
            ->withMin([
                'products as starting_price' => fn ($query) => $query->active()->publicCatalogue()->availableForPurchase(),
            ], 'price')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function show(ShopFilterRequest $request, Category $category): View
    {
        $filters = $request->validated();
        abort_unless(
            $category->is_active
            && $category->products()->active()->publicCatalogue()->exists(),
            404
        );

        $category->loadMissing([
            'latestActiveProduct.images' => fn ($query) => $query
                ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                ->limit(1),
            'latestActiveProduct.images.media:id,path',
        ]);

        $productQuery = $category
            ->products()
            ->active()
            ->publicCatalogue();

        if (! empty($filters['width_cm'])) {
            $productQuery->where('width_cm', (int) $filters['width_cm']);
        }

        if (! empty($filters['installation_type'])) {
            $productQuery->where('installation_type', $filters['installation_type']);
        }

        $products = $productQuery
            ->with([
                'category',
                // Product cards only render the first image; avoid loading full galleries for 12 rows.
                'images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'images.media:id,path',
                'variants:id,product_id,type,stock',
            ])
            ->orderByAvailability()
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $widthOptions = $category->products()
            ->active()
            ->publicCatalogue()
            ->whereNotNull('width_cm')
            ->distinct()
            ->orderBy('width_cm')
            ->pluck('width_cm');

        $installationTypes = $category->products()
            ->active()
            ->publicCatalogue()
            ->whereNotNull('installation_type')
            ->where('installation_type', '!=', '')
            ->distinct()
            ->orderBy('installation_type')
            ->pluck('installation_type');

        return view('categories.show', [
            'category' => $category,
            'products' => $products,
            'widthOptions' => $widthOptions,
            'installationTypes' => $installationTypes,
        ]);
    }
}
