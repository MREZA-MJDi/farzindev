<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\Seo\SeoSettings;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(SeoSettings $seoSettings): Response
    {
        abort_unless($seoSettings->sitemapEnabled(), 404);

        $xml = Cache::remember(
            $seoSettings->sitemapCacheKey(),
            now()->addMinutes(30),
            function () use ($seoSettings): string {
                $staticPages = collect([
                    '/',
                    '/shop',
                    '/categories',
                    '/blog',
                    '/about',
                    '/contact',
                ])->map(fn (string $path): array => [
                    'url' => $seoSettings->canonicalUrl($path, false),
                    'lastmod' => null,
                ]);

                $categoryPages = Category::query()
                    ->active()
                    ->orderBy('sort_order')
                    ->get(['slug', 'updated_at'])
                    ->map(fn (Category $category): array => [
                        'url' => $seoSettings->canonicalUrl(route('categories.show', $category->slug), false),
                        'lastmod' => $category->updated_at?->toAtomString(),
                    ]);

                $productPages = Product::query()
                    ->active()
                    ->whereHas('category', fn ($categoryQuery) => $categoryQuery
                        ->where('is_active', true))
                    ->orderBy('id')
                    ->get(['slug', 'updated_at'])
                    ->map(fn (Product $product): array => [
                        'url' => $seoSettings->canonicalUrl(route('product.show', $product->slug), false),
                        'lastmod' => $product->updated_at?->toAtomString(),
                    ]);

                $items = $staticPages
                    ->concat($categoryPages)
                    ->concat($productPages)
                    ->unique('url')
                    ->values();

                return view('seo.sitemap', ['items' => $items])->render();
            }
        );

        return response($xml)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=1800');
    }
}
