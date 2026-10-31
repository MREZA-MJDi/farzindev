<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use App\Services\Seo\SeoSettings;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    private const MAX_URLS_PER_SITEMAP = 50000;
    private const PREFERRED_SEGMENT_SIZE = 10000;

    public function __invoke(SeoSettings $seoSettings): Response
    {
        abort_unless($seoSettings->sitemapEnabled(), 404);

        $counts = $this->publicUrlCounts();
        $total = array_sum($counts);
        $fingerprint = $this->publicContentFingerprint($seoSettings, $counts);

        $xml = Cache::remember(
            $seoSettings->sitemapCacheKey() . '.' . $fingerprint,
            now()->addMinutes(30),
            function () use ($seoSettings, $counts, $total): string {
                if ($total > self::MAX_URLS_PER_SITEMAP) {
                    return view('seo.sitemap-index', [
                        'sitemaps' => $this->sitemapSegments($seoSettings, $counts, $total),
                    ])->render();
                }

                return view('seo.sitemap', [
                    'items' => $this->collectAllPublicUrls(),
                ])->render();
            }
        );

        return $this->xmlResponse($xml);
    }

    public function segment(string $type, int $page, SeoSettings $seoSettings): Response
    {
        abort_unless($seoSettings->sitemapEnabled(), 404);

        $counts = $this->publicUrlCounts();
        abort_unless(array_key_exists($type, $counts), 404);

        $fingerprint = $this->publicContentFingerprint($seoSettings, $counts);
        $count = (int) $counts[$type];
        $total = array_sum($counts);
        $segmentSize = $this->segmentSize($total);
        $pageCount = (int) ceil($count / $segmentSize);

        abort_unless($page >= 1 && $page <= $pageCount, 404);

        $cacheKey = 'storefront.sitemap.segment.' . $fingerprint . '.' . $type . '.' . $page;

        $xml = Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            function () use ($type, $page, $segmentSize): string {
                $items = $this->collectSegment($type, $page, $segmentSize);

                return view('seo.sitemap', ['items' => $items])->render();
            }
        );

        return $this->xmlResponse($xml);
    }

    /**
     * Count only publicly indexable URLs. This is constant-memory and is used
     * to decide whether a normal sitemap or a sitemap index should be served.
     *
     * @return array<string, int>
     */
    private function publicUrlCounts(): array
    {
        return [
            'pages' => 6,
            'categories' => Category::query()->active()->count(),
            'products' => Product::query()
                ->active()
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->count(),
            'blog' => BlogPost::query()->published()->count(),
        ];
    }

    /**
     * Include canonical host and latest public-content timestamps so edits to
     * slugs or canonical settings cannot keep serving stale sitemap URLs.
     *
     * @param array<string, int> $counts
     */
    private function publicContentFingerprint(SeoSettings $seoSettings, array $counts): string
    {
        return md5(json_encode([
            'counts' => $counts,
            'canonical' => $seoSettings->canonicalUrl('/', false),
            'categories_updated_at' => Category::query()->active()->max('updated_at'),
            'products_updated_at' => Product::query()
                ->active()
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->max('updated_at'),
            'blog_updated_at' => BlogPost::query()->published()->max('updated_at'),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function segmentSize(int $total): int
    {
        if ($total <= self::MAX_URLS_PER_SITEMAP) {
            return self::PREFERRED_SEGMENT_SIZE;
        }

        // Keep every sitemap at or below the protocol limit and the index
        // itself at or below 50,000 entries, even for very large catalogues.
        $maximumSegments = self::MAX_URLS_PER_SITEMAP - 4;

        return min(
            self::MAX_URLS_PER_SITEMAP,
            max(self::PREFERRED_SEGMENT_SIZE, (int) ceil($total / $maximumSegments))
        );
    }

    /**
     * @param array<string, int> $counts
     * @return array<int, array{url: string, lastmod: string}>
     */
    private function sitemapSegments(SeoSettings $seoSettings, array $counts, int $total): array
    {
        $segmentSize = $this->segmentSize($total);
        $sitemaps = [];

        foreach ($counts as $type => $count) {
            $pages = (int) ceil($count / $segmentSize);

            for ($page = 1; $page <= $pages; $page++) {
                $path = route('sitemap.segment', [
                    'type' => $type,
                    'page' => $page,
                ]);

                $sitemaps[] = [
                    'url' => $seoSettings->canonicalUrl($path, false),
                    'lastmod' => now()->toAtomString(),
                ];
            }
        }

        return $sitemaps;
    }

    /**
     * @return Collection<int, array{url: string, lastmod: string|null}>
     */
    private function collectAllPublicUrls(): Collection
    {
        $items = $this->staticPages();

        Category::query()
            ->active()
            ->orderBy('id')
            ->chunkById(1000, function ($categories) use ($items): void {
                foreach ($categories as $category) {
                    $items->push([
                        'url' => app(SeoSettings::class)->canonicalUrl(
                            route('categories.show', $category->slug),
                            false
                        ),
                        'lastmod' => $category->updated_at?->toAtomString(),
                    ]);
                }
            });

        Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->orderBy('id')
            ->chunkById(1000, function ($products) use ($items): void {
                foreach ($products as $product) {
                    $items->push([
                        'url' => app(SeoSettings::class)->canonicalUrl(
                            route('product.show', $product->slug),
                            false
                        ),
                        'lastmod' => $product->updated_at?->toAtomString(),
                    ]);
                }
            });

        BlogPost::query()
            ->published()
            ->orderBy('id')
            ->chunkById(1000, function ($posts) use ($items): void {
                foreach ($posts as $post) {
                    $items->push([
                        'url' => app(SeoSettings::class)->canonicalUrl(
                            route('blog.show', $post->slug),
                            false
                        ),
                        'lastmod' => $post->updated_at?->toAtomString(),
                    ]);
                }
            });

        return $items->unique('url')->values();
    }

    /**
     * @return Collection<int, array{url: string, lastmod: string|null}>
     */
    private function collectSegment(string $type, int $page, int $segmentSize): Collection
    {
        $offset = ($page - 1) * $segmentSize;
        $seoSettings = app(SeoSettings::class);

        if ($type === 'pages') {
            return $this->staticPages();
        }

        if ($type === 'categories') {
            return Category::query()
                ->active()
                ->orderBy('id')
                ->offset($offset)
                ->limit($segmentSize)
                ->get(['id', 'slug', 'updated_at'])
                ->map(fn (Category $category): array => [
                    'url' => $seoSettings->canonicalUrl(route('categories.show', $category->slug), false),
                    'lastmod' => $category->updated_at?->toAtomString(),
                ]);
        }

        if ($type === 'products') {
            return Product::query()
                ->active()
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->orderBy('id')
                ->offset($offset)
                ->limit($segmentSize)
                ->get(['id', 'slug', 'updated_at'])
                ->map(fn (Product $product): array => [
                    'url' => $seoSettings->canonicalUrl(route('product.show', $product->slug), false),
                    'lastmod' => $product->updated_at?->toAtomString(),
                ]);
        }

        return BlogPost::query()
            ->published()
            ->orderBy('id')
            ->offset($offset)
            ->limit($segmentSize)
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (BlogPost $post): array => [
                'url' => $seoSettings->canonicalUrl(route('blog.show', $post->slug), false),
                'lastmod' => $post->updated_at?->toAtomString(),
            ]);
    }

    /**
     * @return Collection<int, array{url: string, lastmod: string|null}>
     */
    private function staticPages(): Collection
    {
        $seoSettings = app(SeoSettings::class);

        return collect([
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
    }

    private function xmlResponse(string $xml): Response
    {
        return response($xml)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=1800');
    }
}
