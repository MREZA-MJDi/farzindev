<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SeoRedirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function show(string $slug): View|RedirectResponse
    {
        $product = Product::query()
            ->with([
                'category',
                'images.media',
                'variants.images.media',
            ])
            ->active()
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery
                ->where('is_active', true))
            ->where('slug', $slug)
            ->first();

        if (! $product) {
            $redirect = SeoRedirect::query()
                ->where('source_path', route('product.show', $slug, false))
                ->where('is_active', true)
                ->first();

            if ($redirect) {
                return redirect()->to($redirect->target_url, $redirect->status_code);
            }

            abort(404);
        }

        $relatedProducts = Product::query()
            ->active()
            ->with([
                'category',
                // Related-product cards only render one image; keep the main product gallery complete.
                'images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'images.media:id,path',
                'variants:id,product_id,stock',
            ])
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery
                ->where('is_active', true))
            ->latest()
            ->limit(4)
            ->get();

        return view('product.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
