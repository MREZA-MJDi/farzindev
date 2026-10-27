<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    @php
        $seoService = app(\App\Services\Seo\SeoSettings::class);
        $seoValues = $seoService->all();

        $seoTitle = trim((string) $__env->yieldContent('title'));
        $seoTitle = $seoTitle !== '' ? $seoTitle : (string) $seoValues['seo_default_title'];

        $seoDescription = trim((string) $__env->yieldContent('description'));
        $seoDescription = $seoDescription !== '' ? $seoDescription : (string) $seoValues['seo_default_description'];

        $seoKeywords = trim((string) $__env->yieldContent('keywords'));
        $seoKeywords = $seoKeywords !== '' ? $seoKeywords : trim((string) ($seoValues['seo_default_keywords'] ?? ''));

        $seoCanonicalSource = trim((string) $__env->yieldContent('canonical'));
        $seoCanonical = $seoService->canonicalUrl($seoCanonicalSource !== '' ? $seoCanonicalSource : request()->getPathInfo());

        $seoOgType = trim((string) $__env->yieldContent('og_type')) ?: 'website';
        $seoPageOgImage = trim((string) $__env->yieldContent('og_image'));
        $seoOgImage = $seoService->resolvePublicAssetUrl(
            $seoPageOgImage !== '' ? $seoPageOgImage : ($seoValues['seo_default_og_image'] ?? null)
        );

        $seoBaseUrl = $seoService->canonicalUrl('/', false);
        $seoOrganizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => filled($seoValues['seo_organization_name'] ?? null)
                ? $seoValues['seo_organization_name']
                : $seoValues['seo_site_name'],
            'url' => $seoBaseUrl,
        ];

        if (filled($seoValues['seo_organization_logo_url'] ?? null)) {
            $seoOrganizationSchema['logo'] = $seoValues['seo_organization_logo_url'];
        }
        if (filled($seoValues['seo_organization_phone'] ?? null)) {
            $seoOrganizationSchema['telephone'] = $seoValues['seo_organization_phone'];
        }
        if (filled($seoValues['seo_organization_email'] ?? null)) {
            $seoOrganizationSchema['email'] = $seoValues['seo_organization_email'];
        }
        if (filled($seoValues['seo_organization_address'] ?? null)) {
            $seoOrganizationSchema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $seoValues['seo_organization_address'],
            ];
        }

        $seoWebsiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $seoValues['seo_site_name'],
            'url' => $seoBaseUrl,
            'inLanguage' => 'fa-IR',
            'description' => $seoValues['seo_default_description'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => rtrim($seoBaseUrl, '/') . '/shop?search={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    @endphp

    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    @if($seoKeywords !== '')
        <meta name="keywords" content="{{ $seoKeywords }}">
    @endif

    <meta name="robots" content="{{ $seoService->robotsMeta() }}">
    <link rel="canonical" href="{{ $seoCanonical }}">

    <meta property="og:type" content="{{ $seoOgType }}">
    <meta property="og:site_name" content="{{ $seoValues['seo_site_name'] }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    @if($seoOgImage)
        <meta property="og:image" content="{{ $seoOgImage }}">
        <meta name="twitter:image" content="{{ $seoOgImage }}">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    @if(filled($seoValues['seo_twitter_site'] ?? null))
        <meta name="twitter:site" content="{{ $seoValues['seo_twitter_site'] }}">
    @endif
    @if(filled($seoValues['seo_google_site_verification'] ?? null))
        <meta name="google-site-verification" content="{{ $seoValues['seo_google_site_verification'] }}">
    @endif
    @if(filled($seoValues['seo_bing_site_verification'] ?? null))
        <meta name="msvalidate.01" content="{{ $seoValues['seo_bing_site_verification'] }}">
    @endif

    @foreach ($seoService->customMetaTags() as $customMetaTag)
        <meta {{ $customMetaTag['type'] }}="{{ $customMetaTag['key'] }}" content="{{ $customMetaTag['content'] }}">
    @endforeach
    @foreach ($seoService->customLinkTags() as $customLinkTag)
        <link rel="{{ $customLinkTag['rel'] }}" href="{{ $customLinkTag['href'] }}"
            @if(filled($customLinkTag['hreflang'])) hreflang="{{ $customLinkTag['hreflang'] }}" @endif
            @if(filled($customLinkTag['type'])) type="{{ $customLinkTag['type'] }}" @endif
            @if(filled($customLinkTag['as'])) as="{{ $customLinkTag['as'] }}" @endif
            @if(filled($customLinkTag['crossorigin'])) crossorigin="{{ $customLinkTag['crossorigin'] }}" @endif
        >
    @endforeach

    <script type="application/ld+json">{!! json_encode($seoWebsiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>
    <script type="application/ld+json">{!! json_encode($seoOrganizationSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>

    @stack('seo')

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('styles')
    @vite(['resources/css/responsive.css'])
</head>

<body class="@yield('body_class', 'min-h-screen antialiased')">

<div
    id="app"
    x-data="{
        mobileOpen: false,
        searchOpen: false,
        searchReturnFocus: null,
        filterOpen: false,
        openSearch(trigger) {
            this.searchReturnFocus = trigger || null;
            this.mobileOpen = false;
            this.searchOpen = true;
            this.$nextTick(() => {
                document.getElementById('global-search')?.focus({ preventScroll: true });
            });
        },
        closeSearch() {
            this.searchOpen = false;
            this.$nextTick(() => {
                this.searchReturnFocus?.focus?.({ preventScroll: true });
            });
        }
    }"
    class="min-h-screen flex flex-col pb-24 lg:pb-0"
>

    <x-layout.navbar />

    <x-layout.mobile-menu />

    <x-layout.search-overlay />

    <x-shop.filter-drawer />

    <main id="main-content" class="storefront-content flex-1">
        @yield('content')
    </main>

    <x-layout.footer />

    <x-layout.mobile-bottom-bar />

</div>

@stack('scripts')

</body>
</html>
