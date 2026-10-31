<?php

namespace App\Services\Seo;

use App\Models\StorefrontSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SeoSettings
{
    private const CACHE_KEY = 'storefront.seo-settings.v1';
    private const SITEMAP_CACHE_KEY = 'storefront.sitemap.xml.v1';

    /** @return array<string, string|null> */
    public function all(): array
    {
        $defaults = $this->defaults();
        $stored = Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(5),
            static fn (): array => StorefrontSetting::query()
                ->whereIn('key', array_keys($defaults))
                ->pluck('value', 'key')
                ->all()
        );

        return array_replace($defaults, $stored);
    }

    public function canonicalUrl(string $path = '/', bool $preservePagination = true): string
    {
        $settings = $this->all();
        $base = (string) ($settings['seo_canonical_base_url'] ?: config('app.url', 'https://farajihome.com'));
        $parts = parse_url($base) ?: [];
        $scheme = strtolower((string) ($parts['scheme'] ?? 'https'));
        $host = (string) ($parts['host'] ?? 'farajihome.com');
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $origin = $scheme . '://' . $host . $port;

        $pathOnly = parse_url($path, PHP_URL_PATH);
        $pathOnly = is_string($pathOnly) && $pathOnly !== '' ? $pathOnly : '/';
        $pathOnly = '/' . ltrim($pathOnly, '/');
        $url = $origin . ($pathOnly === '/' ? '/' : $pathOnly);

        $page = $preservePagination ? request()->query('page') : null;
        if (is_scalar($page) && ctype_digit((string) $page) && (int) $page > 1) {
            $url .= '?page=' . (int) $page;
        }

        return $url;
    }

    public function resolvePublicAssetUrl(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

            return in_array($scheme, ['http', 'https'], true) ? $value : null;
        }

        return $this->canonicalUrl(Storage::disk('public')->url(ltrim($value, '/')), false);
    }

    public function robotsMeta(): string
    {
        $settings = $this->all();
        $developmentMode = ($settings['seo_development_mode'] ?? '0') === '1';
        $noIndex = $developmentMode || ($settings['seo_robots_noindex'] ?? '0') === '1';
        $noFollow = $developmentMode || ($settings['seo_robots_nofollow'] ?? '0') === '1';
        $directives = [
            $noIndex ? 'noindex' : 'index',
            $noFollow ? 'nofollow' : 'follow',
        ];

        if ($developmentMode || ($settings['seo_robots_noarchive'] ?? '0') === '1') {
            $directives[] = 'noarchive';
        }
        if ($developmentMode || ($settings['seo_robots_nosnippet'] ?? '0') === '1') {
            $directives[] = 'nosnippet';
        }
        if ($developmentMode || ($settings['seo_robots_noimageindex'] ?? '0') === '1') {
            $directives[] = 'noimageindex';
        }

        if (! $developmentMode) {
            if (($settings['seo_robots_nosnippet'] ?? '0') !== '1') {
                $directives[] = 'max-snippet:-1';
            }

            $directives[] = 'max-video-preview:-1';

            if (($settings['seo_robots_noimageindex'] ?? '0') !== '1') {
                $directives[] = 'max-image-preview:large';
            }
        }

        return implode(', ', $directives);
    }

    public function sitemapEnabled(): bool
    {
        return ($this->all()['seo_sitemap_enabled'] ?? '1') === '1';
    }

    public function robotsTxt(): string
    {
        $settings = $this->all();
        $rules = trim(str_replace(["\r\n", "\r"], "\n", (string) $settings['seo_robots_txt_rules']));
        $lines = $rules === '' ? ['User-agent: *', 'Allow: /'] : explode("\n", $rules);

        if ($this->sitemapEnabled()) {
            $lines[] = '';
            $lines[] = 'Sitemap: ' . $this->canonicalUrl('/sitemap.xml', false);
        }

        return rtrim(implode("\n", $lines)) . "\n";
    }

    /**
     * @return array<int, array{type: string, key: string, content: string}>
     */
    public function customMetaTags(): array
    {
        $rows = $this->decodeStoredList($this->all()['seo_custom_meta_tags'] ?? '[]');
        $reserved = [
            'name' => [
                'description', 'keywords', 'robots', 'google-site-verification',
                'msvalidate.01', 'viewport', 'referrer', 'twitter:title', 'twitter:description',
                'twitter:card', 'twitter:image', 'twitter:site',
            ],
            'property' => [
                'og:type', 'og:site_name', 'og:title', 'og:description', 'og:url', 'og:image',
            ],
        ];
        $tags = [];

        foreach ($rows as $row) {
            $type = strtolower(trim((string) ($row['type'] ?? '')));
            $key = trim((string) ($row['key'] ?? ''));
            $content = trim((string) ($row['content'] ?? ''));

            if (
                ! in_array($type, ['name', 'property'], true)
                || $key === ''
                || $content === ''
                || ! preg_match('/^[A-Za-z][A-Za-z0-9:._-]{0,99}$/D', $key)
                || in_array(strtolower($key), $reserved[$type], true)
            ) {
                continue;
            }

            $tags[] = ['type' => $type, 'key' => $key, 'content' => $content];
        }

        return $tags;
    }

    /**
     * @return array<int, array{rel: string, href: string, hreflang: string, type: string, as: string, crossorigin: string}>
     */
    public function customLinkTags(): array
    {
        $rows = $this->decodeStoredList($this->all()['seo_custom_link_tags'] ?? '[]');
        $allowedRels = [
            'alternate', 'apple-touch-icon', 'author', 'dns-prefetch', 'help', 'icon',
            'license', 'manifest', 'mask-icon', 'modulepreload', 'preconnect', 'prefetch',
            'preload', 'stylesheet',
        ];
        $allowedAs = ['audio', 'document', 'embed', 'fetch', 'font', 'image', 'object', 'script', 'style', 'track', 'video', 'worker'];
        $tags = [];

        foreach ($rows as $row) {
            $rel = strtolower(trim((string) ($row['rel'] ?? '')));
            $href = trim((string) ($row['href'] ?? ''));
            $hreflang = trim((string) ($row['hreflang'] ?? ''));
            $type = trim((string) ($row['type'] ?? ''));
            $as = strtolower(trim((string) ($row['as'] ?? '')));
            $crossorigin = strtolower(trim((string) ($row['crossorigin'] ?? '')));

            if (
                $href === ''
                || ! in_array($rel, $allowedRels, true)
                || ! $this->isSafeCustomHref($href)
                || ($hreflang !== '' && ! preg_match('/^[A-Za-z]{2,3}(?:-[A-Za-z0-9]{2,8})*$/D', $hreflang))
                || ($type !== '' && ! preg_match('/^[A-Za-z0-9.+\/-]{1,100}$/D', $type))
                || ($as !== '' && (! in_array($as, $allowedAs, true) || ! in_array($rel, ['preload', 'modulepreload'], true)))
                || ($crossorigin !== '' && ! in_array($crossorigin, ['anonymous', 'use-credentials'], true))
            ) {
                continue;
            }

            $tags[] = [
                'rel' => $rel,
                'href' => $href,
                'hreflang' => $hreflang,
                'type' => $type,
                'as' => $as,
                'crossorigin' => $crossorigin,
            ];
        }

        return $tags;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function decodeStoredList(?string $value): array
    {
        $decoded = json_decode((string) $value, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, 'is_array'));
    }

    private function isSafeCustomHref(string $href): bool
    {
        if ($href === '' || preg_match('/[\x00-\x20]/', $href) || str_contains($href, chr(92))) {
            return false;
        }

        if (filter_var($href, FILTER_VALIDATE_URL)) {
            return in_array(strtolower((string) parse_url($href, PHP_URL_SCHEME)), ['http', 'https'], true);
        }

        return str_starts_with($href, '/')
            && ! str_starts_with($href, '//')
            && ! preg_match('/[\x00-\x1F]/', $href);
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::SITEMAP_CACHE_KEY);
    }

    public function sitemapCacheKey(): string
    {
        return self::SITEMAP_CACHE_KEY;
    }

    /** @return array<string, string|null> */
    private function defaults(): array
    {
        return [
            'seo_canonical_base_url' => rtrim((string) config('app.url', 'https://farajihome.com'), '/'),
            'seo_site_name' => 'فرزین',
            'seo_default_title' => 'خرید هود و سینک آشپزخانه | فرزین',
            'seo_default_description' => 'خرید هود و سینک آشپزخانه از فرزین؛ مشخصات محصولات، مدل‌ها و گزینه‌های موجود را بررسی و مقایسه کنید.',
            'seo_default_keywords' => 'خرید هود آشپزخانه، خرید سینک ظرفشویی، هود فرزین، سینک فرزین، تجهیزات آشپزخانه',
            'seo_custom_meta_tags' => '[]',
            'seo_custom_link_tags' => '[]',
            'seo_default_og_image' => null,
            'seo_development_mode' => '0',
            'seo_robots_noindex' => '0',
            'seo_robots_nofollow' => '0',
            'seo_robots_noarchive' => '0',
            'seo_robots_nosnippet' => '0',
            'seo_robots_noimageindex' => '0',
            'seo_robots_txt_rules' => "User-agent: *\nAllow: /",
            'seo_sitemap_enabled' => '1',
            'seo_google_site_verification' => null,
            'seo_bing_site_verification' => null,
            'seo_twitter_site' => null,
            'seo_organization_name' => 'فرزین',
            'seo_organization_logo_url' => null,
            'seo_organization_phone' => null,
            'seo_organization_email' => null,
            'seo_organization_address' => null,
        ];
    }
}
