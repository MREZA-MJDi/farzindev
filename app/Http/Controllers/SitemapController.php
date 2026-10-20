<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $staticPages = collect([
            '/',
            '/shop',
            '/categories',
            '/blog',
            '/about',
            '/contact',
        ])->map(fn (string $path): array => [
            'url' => url($path),
            'lastmod' => null,
        ]);

        $categoryPages = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at'])
            ->map(fn (Category $category): array => [
                'url' => route('categories.show', $category->slug),
                'lastmod' => $category->updated_at?->toAtomString(),
            ]);

        $productPages = Product::query()
            ->active()
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery
                ->where('is_active', true))
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->map(fn (Product $product): array => [
                'url' => route('product.show', $product->slug),
                'lastmod' => $product->updated_at?->toAtomString(),
            ]);

        $items = $staticPages
            ->concat($categoryPages)
            ->concat($productPages)
            ->unique('url')
            ->values();

        return response()
            ->view('seo.sitemap', ['items' => $items])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
