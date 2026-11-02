<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ContactSetting;
use Illuminate\Contracts\View\View;

class ProductController extends Controller
{
    public function show(string $slug): View
    {
        $product = Product::query()
            ->with([
                'category',
                'images.media',
                'variants.images.media',
            ])
            ->active()
            ->publicCatalogue()
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery
                ->where('is_active', true))
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedProducts = Product::query()
            ->active()
            ->with([
                'category',
                // Related-product cards only render one image; keep the main product gallery complete.
                'images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'images.media:id,path',
                'variants:id,product_id,type,stock',
            ])
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery
                ->where('is_active', true))
            ->publicCatalogue()
            ->orderByAvailability()
            ->latest()
            ->limit(4)
            ->get();

        $publicContactSettings = ContactSetting::query()
            ->where('is_active', true)
            ->first(['warranty_summary', 'shipping_summary', 'installation_summary']);

        return view('product.show', [
            'publicContactSettings' => $publicContactSettings,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
