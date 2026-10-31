<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogTerm;
use App\Models\Category;
use App\Models\Product;
use App\Services\Seo\SeoSettings;
use Illuminate\Database\Eloquent\Builder;
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
                    'items' => $this->collectAllPublicUrls($seoSettings),
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
            function () use ($type, $page, $segmentSize, $seoSettings): string {
                $items = $this->collectSegment($type, $page, $segmentSize, $seoSettings);

                return view('seo.sitemap', ['items' => $items])->render();
            }
        );

        return $this->xmlResponse($xml);
    }

    /**
     * Count only public and indexable URLs; queries remain constant-memory.
     *
     * @return array<string, int>
     */
    private function publicUrlCounts(): array
    {
        return [
            'pages' => 6,
            'categories' => $this->indexableCategories()->count(),
            'products' => $this->indexableProducts()->count(),
            'blog' => $this->indexableBlogPosts()->count(),
            'blog-categories' => $this->indexableBlogTerms(BlogTerm::TYPE_CATEGORY)->count(),
            'blog-tags' => $this->indexableBlogTerms(BlogTerm::TYPE_TAG)->count(),
        ];
    }

    /**
     * Include content timestamps and SEO term updates in the fingerprint.
     *
     * @param array<string, int> $counts
     */
    private function publicContentFingerprint(SeoSettings $seoSettings, array $counts): string
    {
        return md5(json_encode([
            'counts' => $counts,
            'canonical' => $seoSettings->canonicalUrl('/', false),
            'categories_updated_at' => $this->indexableCategories()->max('updated_at'),
            'products_updated_at' => $this->indexableProducts()->max('updated_at'),
            'blog_updated_at' => $this->indexableBlogPosts()->max('updated_at'),
            'blog_terms_updated_at' => BlogTerm::query()
                ->active()
                ->whereIn('seo_robots', ['inherit', 'index,follow'])
                ->max('updated_at'),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function indexableCategories(): Builder
    {
        return Category::query()
            ->active()
            ->whereIn('seo_robots', ['inherit', 'index,follow'])
            ->where(function (Builder $query): void {
                $query->whereNull('canonical_url')
                    ->orWhere('canonical_url', '')
                    ->orWhere('canonical_url', 'like', '/%');
            });
    }

    private function indexableProducts(): Builder
    {
        return Product::query()
            ->active()
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->whereIn('seo_robots', ['inherit', 'index,follow'])
            ->where(function (Builder $query): void {
                $query->whereNull('canonical_url')
                    ->orWhere('canonical_url', '')
                    ->orWhere('canonical_url', 'like', '/%');
            });
    }

    private function indexableBlogPosts(): Builder
    {
        return BlogPost::query()
            ->published()
            ->whereIn('seo_robots', ['inherit', 'index,follow'])
            ->where(function (Builder $query): void {
                $query->whereNull('canonical_url')
                    ->orWhere('canonical_url', '')
                    ->orWhere('canonical_url', 'like', '/%');
            });
    }

    private function indexableBlogTerms(string $type): Builder
    {
        return BlogTerm::query()
            ->active()
            ->ofType($type)
            ->whereIn('seo_robots', ['inherit', 'index,follow'])
            ->whereHas('posts', function (Builder $query): void {
                $query->published()
                    ->whereIn('seo_robots', ['inherit', 'index,follow'])
                    ->where(function (Builder $canonical): void {
                        $canonical->whereNull('canonical_url')
                            ->orWhere('canonical_url', '')
                            ->orWhere('canonical_url', 'like', '/%');
                    });
            });
    }

    private function segmentSize(int $total): int
    {
        if ($total <= self::MAX_URLS_PER_SITEMAP) {
            return self::PREFERRED_SEGMENT_SIZE;
        }

        // Keep each XML file and the sitemap index within the protocol limit.
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
    private function collectAllPublicUrls(SeoSettings $seoSettings): Collection
    {
        $items = $this->staticPages();
        $canonicalRoot = $seoSettings->canonicalUrl('/', false);

        $this->indexableCategories()
            ->orderBy('id')
            ->select(['id', 'slug', 'canonical_url', 'updated_at'])
            ->chunkById(1000, function ($categories) use ($items, $canonicalRoot, $seoSettings): void {
                foreach ($categories as $category) {
                    $canonical = filled($category->canonical_url)
                        ? $seoSettings->canonicalOverride($category->canonical_url)
                        : null;

                    $items->push([
                        'url' => $canonical ?: $this->canonicalPath(
                            $canonicalRoot,
                            route('categories.show', $category->slug, false)
                        ),
                        'lastmod' => $category->updated_at?->toAtomString(),
                    ]);
                }
            });

        $this->indexableProducts()
            ->orderBy('id')
            ->select(['id', 'slug', 'canonical_url', 'updated_at'])
            ->chunkById(1000, function ($products) use ($items, $canonicalRoot, $seoSettings): void {
                foreach ($products as $product) {
                    $canonical = filled($product->canonical_url)
                        ? $seoSettings->canonicalOverride($product->canonical_url)
                        : null;

                    $items->push([
                        'url' => $canonical ?: $this->canonicalPath(
                            $canonicalRoot,
                            route('product.show', $product->slug, false)
                        ),
                        'lastmod' => $product->updated_at?->toAtomString(),
                    ]);
                }
            });

        $this->indexableBlogPosts()
            ->orderBy('id')
            ->select(['id', 'slug', 'canonical_url', 'updated_at'])
            ->chunkById(1000, function ($posts) use ($items, $canonicalRoot, $seoSettings): void {
                foreach ($posts as $post) {
                    $path = filled($post->canonical_url)
                        ? $seoSettings->canonicalOverride($post->canonical_url)
                        : null;

                    $items->push([
                        'url' => $path ?: $this->canonicalPath(
                            $canonicalRoot,
                            route('blog.show', $post->slug, false)
                        ),
                        'lastmod' => $post->updated_at?->toAtomString(),
                    ]);
                }
            });

        $this->pushBlogTermUrls($items, BlogTerm::TYPE_CATEGORY, $canonicalRoot);
        $this->pushBlogTermUrls($items, BlogTerm::TYPE_TAG, $canonicalRoot);

        return $items->unique('url')->values();
    }

    /**
     * @param Collection<int, array{url: string, lastmod: string|null}> $items
     */
    private function pushBlogTermUrls(Collection $items, string $type, string $canonicalRoot): void
    {
        $this->indexableBlogTerms($type)
            ->orderBy('id')
            ->select(['id', 'slug', 'type', 'updated_at'])
            ->chunkById(1000, function ($terms) use ($items, $canonicalRoot, $type): void {
                foreach ($terms as $term) {
                    $path = $type === BlogTerm::TYPE_CATEGORY
                        ? route('blog.category', $term->slug, false)
                        : route('blog.tag', $term->slug, false);

                    $items->push([
                        'url' => $this->canonicalPath($canonicalRoot, $path),
                        'lastmod' => $term->updated_at?->toAtomString(),
                    ]);
                }
            });
    }

    /**
     * @return Collection<int, array{url: string, lastmod: string|null}>
     */
    private function collectSegment(string $type, int $page, int $segmentSize, SeoSettings $seoSettings): Collection
    {
        $offset = ($page - 1) * $segmentSize;
        $canonicalRoot = $seoSettings->canonicalUrl('/', false);

        if ($type === 'pages') {
            return $this->staticPages();
        }

        if ($type === 'categories') {
            return $this->indexableCategories()
                ->orderBy('id')
                ->offset($offset)
                ->limit($segmentSize)
                ->get(['id', 'slug', 'canonical_url', 'updated_at'])
                ->map(function (Category $category) use ($canonicalRoot, $seoSettings): array {
                    $canonical = filled($category->canonical_url)
                        ? $seoSettings->canonicalOverride($category->canonical_url)
                        : null;

                    return [
                        'url' => $canonical ?: $this->canonicalPath($canonicalRoot, route('categories.show', $category->slug, false)),
                        'lastmod' => $category->updated_at?->toAtomString(),
                    ];
                });
        }

        if ($type === 'products') {
            return $this->indexableProducts()
                ->orderBy('id')
                ->offset($offset)
                ->limit($segmentSize)
                ->get(['id', 'slug', 'canonical_url', 'updated_at'])
                ->map(function (Product $product) use ($canonicalRoot, $seoSettings): array {
                    $canonical = filled($product->canonical_url)
                        ? $seoSettings->canonicalOverride($product->canonical_url)
                        : null;

                    return [
                        'url' => $canonical ?: $this->canonicalPath($canonicalRoot, route('product.show', $product->slug, false)),
                        'lastmod' => $product->updated_at?->toAtomString(),
                    ];
                });
        }

        if ($type === 'blog') {
            return $this->indexableBlogPosts()
                ->orderBy('id')
                ->offset($offset)
                ->limit($segmentSize)
                ->get(['id', 'slug', 'canonical_url', 'updated_at'])
                ->map(function (BlogPost $post) use ($canonicalRoot, $seoSettings): array {
                    $canonical = filled($post->canonical_url)
                        ? $seoSettings->canonicalOverride($post->canonical_url)
                        : null;

                    return [
                        'url' => $canonical ?: $this->canonicalPath($canonicalRoot, route('blog.show', $post->slug, false)),
                        'lastmod' => $post->updated_at?->toAtomString(),
                    ];
                });
        }

        $termType = $type === 'blog-categories'
            ? BlogTerm::TYPE_CATEGORY
            : BlogTerm::TYPE_TAG;

        return $this->indexableBlogTerms($termType)
            ->orderBy('id')
            ->offset($offset)
            ->limit($segmentSize)
            ->get(['id', 'slug', 'type', 'updated_at'])
            ->map(function (BlogTerm $term) use ($canonicalRoot, $termType): array {
                $path = $termType === BlogTerm::TYPE_CATEGORY
                    ? route('blog.category', $term->slug, false)
                    : route('blog.tag', $term->slug, false);

                return [
                    'url' => $this->canonicalPath($canonicalRoot, $path),
                    'lastmod' => $term->updated_at?->toAtomString(),
                ];
            });
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

    private function canonicalPath(string $canonicalRoot, string $path): string
    {
        $pathOnly = parse_url($path, PHP_URL_PATH);
        $pathOnly = is_string($pathOnly) && $pathOnly !== '' ? $pathOnly : '/';

        return rtrim($canonicalRoot, '/') . '/' . ltrim($pathOnly, '/');
    }

    private function xmlResponse(string $xml): Response
    {
        return response($xml)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=1800');
    }
}
