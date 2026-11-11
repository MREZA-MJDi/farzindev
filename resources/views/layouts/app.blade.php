<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    @php
        $seoService = app(\App\Services\Seo\SeoSettings::class);
        $seoValues = $seoService->all();

        $seoRouteName = request()->route()?->getName();
        $seoStaticPage = match ($seoRouteName) {
            'home' => ['title' => 'seo_home_title', 'description' => 'seo_home_description'],
            'shop.index' => ['title' => 'seo_shop_title', 'description' => 'seo_shop_description'],
            'categories.index' => ['title' => 'seo_categories_title', 'description' => 'seo_categories_description'],
            'blog.index' => ['title' => 'seo_blog_title', 'description' => 'seo_blog_description'],
            'about' => ['title' => 'seo_about_title', 'description' => 'seo_about_description'],
            'contact' => ['title' => 'seo_contact_title', 'description' => 'seo_contact_description'],
            default => null,
        };

        $storefrontBrandName = $storefrontBrandName ?? config('app.name', 'فرزین');
        $seoTitle = trim((string) $__env->yieldContent('title'));
        $seoTitle = str_replace('فرزین', $storefrontBrandName, $seoTitle);
        $seoPageTitle = $seoStaticPage ? trim((string) ($seoValues[$seoStaticPage['title']] ?? '')) : '';
        $seoPageTitle = str_replace('فرزین', $storefrontBrandName, $seoPageTitle);
        $seoTitle = $seoPageTitle !== ''
            ? $seoPageTitle
            : ($seoTitle !== '' ? $seoTitle : (string) $seoValues['seo_default_title']);

        $seoTitle = str_replace('فرزین', $storefrontBrandName, $seoTitle);
        $seoDescription = trim((string) $__env->yieldContent('description'));
        $seoPageDescription = $seoStaticPage ? trim((string) ($seoValues[$seoStaticPage['description']] ?? '')) : '';
        $seoDescription = $seoPageDescription !== ''
            ? $seoPageDescription
            : ($seoDescription !== '' ? $seoDescription : (string) $seoValues['seo_default_description']);

        $seoKeywords = trim((string) $__env->yieldContent('keywords'));
        $seoKeywords = $seoKeywords !== '' ? $seoKeywords : trim((string) ($seoValues['seo_default_keywords'] ?? ''));

        $seoCanonicalSource = trim((string) $__env->yieldContent('canonical'));
        $seoCanonical = $seoService->canonicalOverride($seoCanonicalSource)
            ?? $seoService->canonicalUrl($seoCanonicalSource !== '' ? $seoCanonicalSource : request()->getPathInfo());
        $seoRobotsOverride = trim((string) $__env->yieldContent('robots'));

        $seoOgType = trim((string) $__env->yieldContent('og_type')) ?: 'website';
        $seoOgTitle = trim((string) $__env->yieldContent('og_title')) ?: $seoTitle;
        $seoOgDescription = trim((string) $__env->yieldContent('og_description')) ?: $seoDescription;
        $seoTwitterCard = trim((string) $__env->yieldContent('twitter_card')) ?: 'summary_large_image';
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
                : $storefrontBrandName,
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

        // Location details are shared with the Contact page and cached so the
        // structured data does not add a database query to every public request.
        $seoContactLocation = \Illuminate\Support\Facades\Cache::remember(
            'storefront.seo-contact-location.v1',
            now()->addMinutes(10),
            static function (): array {
                $contact = \App\Models\ContactSetting::query()
                    ->where('is_active', true)
                    ->first(['address', 'latitude', 'longitude']);

                return [
                    'address' => $contact?->address,
                    'latitude' => $contact?->latitude,
                    'longitude' => $contact?->longitude,
                ];
            }
        );

        if (
            is_numeric($seoContactLocation['latitude'] ?? null)
            && is_numeric($seoContactLocation['longitude'] ?? null)
        ) {
            $coordinates = (string) $seoContactLocation['latitude'] . ',' . (string) $seoContactLocation['longitude'];
            $seoOrganizationSchema['@type'] = 'LocalBusiness';
            $seoOrganizationSchema['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $seoContactLocation['latitude'],
                'longitude' => (float) $seoContactLocation['longitude'],
            ];

            if (! filled($seoValues['seo_organization_address'] ?? null) && filled($seoContactLocation['address'] ?? null)) {
                $seoOrganizationSchema['address'] = [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $seoContactLocation['address'],
                ];
            }
        }

        $seoWebsiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $storefrontBrandName,
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

    {{-- Warm the two storefront font weights used by body copy and bold headings to reduce first-paint reflow. --}}
    <link rel="preload" as="font" href="{{ Vite::asset('resources/fonts/iranyekanx-fanum/IRANYekanXFaNum-Regular.woff2') }}" type="font/woff2" crossorigin>
    <link rel="preload" as="font" href="{{ Vite::asset('resources/fonts/iranyekanx-fanum/IRANYekanXFaNum-ExtraBold.woff2') }}" type="font/woff2" crossorigin>

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    @if($seoKeywords !== '')
        <meta name="keywords" content="{{ $seoKeywords }}">
    @endif

    <meta name="robots" content="{{ $seoService->robotsMeta($seoRobotsOverride) }}">
    <link rel="canonical" href="{{ $seoCanonical }}">

    <meta property="og:type" content="{{ $seoOgType }}">
    <meta property="og:site_name" content="{{ $storefrontBrandName }}">
    <meta property="og:title" content="{{ $seoOgTitle }}">
    <meta property="og:description" content="{{ $seoOgDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    @if($seoOgImage)
        <meta property="og:image" content="{{ $seoOgImage }}">
        <meta name="twitter:image" content="{{ $seoOgImage }}">
        <meta name="twitter:card" content="{{ $seoTwitterCard }}">
    @else
        <meta name="twitter:card" content="{{ $seoTwitterCard }}">
    @endif
    <meta name="twitter:title" content="{{ $seoOgTitle }}">
    <meta name="twitter:description" content="{{ $seoOgDescription }}">
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
        'resources/css/storefront.css',
        'resources/js/app.js',
    ])

    @stack('styles')

    @vite('resources/css/public-design-system.css')
</head>

<body class="@yield('body_class', 'min-h-screen antialiased')">

<div
    id="app"
    x-data="{
        mobileOpen: false,
        searchOpen: false,
        searchReturnFocus: null,
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
    x-effect="document.body.classList.toggle('overflow-hidden', mobileOpen || searchOpen)"
    class="min-h-screen flex flex-col pb-24 lg:pb-0"
>

    @php
        // Shared, short-lived public contact payload for the global footer.
        $storefrontContact = \Illuminate\Support\Facades\Cache::remember(
            'storefront.public-contact.v1',
            now()->addMinutes(10),
            static function (): array {
                $contact = \App\Models\ContactSetting::query()
                    ->where('is_active', true)
                    ->first(['phone', 'email', 'whatsapp', 'instagram_url', 'telegram_url', 'address', 'hours']);

                return [
                    'phone' => $contact?->phone,
                    'email' => $contact?->email,
                    'whatsapp' => $contact?->whatsapp,
                    'instagram_url' => $contact?->instagram_url,
                    'telegram_url' => $contact?->telegram_url,
                    'address' => $contact?->address,
                    'hours' => $contact?->hours,
                ];
            }
        );

        // One aggregate query feeds both responsive cart counters.
        // Avoid loading every cart-item row twice from separate layout components.
        $storefrontCartCount = 0;

        if (auth()->check() && auth()->user()->isCustomer()) {
            $cartSummary = auth()->user()
                ->activeCart()
                ->withSum('items', 'quantity')
                ->first();

            $storefrontCartCount = (int) ($cartSummary?->items_sum_quantity ?? 0);
        }
    @endphp

    @if(request()->routeIs('home'))
        <x-layout.home-navbar
            :cart-count="$storefrontCartCount"
            :brand-name="$storefrontBrandName"
            :brand-logo="$storefrontBrandLogoUrl"
            :categories="$categories ?? null"
        />
    @else
        <x-layout.navbar :cart-count="$storefrontCartCount" :brand-name="$storefrontBrandName" :brand-logo="$storefrontBrandLogoUrl" />
    @endif

    <x-layout.mobile-menu :brand-name="$storefrontBrandName" :brand-logo="$storefrontBrandLogoUrl" />

    <x-layout.search-overlay />

    <main id="main-content" class="storefront-content flex-1">
        @yield('content')
    </main>

    <x-layout.footer :contact="$storefrontContact" :settings="$storefrontPublicSettings" :brand-logo-url="$storefrontBrandLogoUrl" />

    <x-layout.mobile-bottom-bar :cart-count="$storefrontCartCount" />

    @php
        $floatingPhone = preg_replace('/[^0-9+]/', '', (string) data_get($storefrontContact, 'phone', ''));
        $floatingWhatsapp = preg_replace('/\D+/', '', (string) data_get($storefrontContact, 'whatsapp', ''));
        if (str_starts_with($floatingWhatsapp, '0098')) {
            $floatingWhatsapp = substr($floatingWhatsapp, 2);
        } elseif ($floatingWhatsapp !== '' && str_starts_with($floatingWhatsapp, '0')) {
            $floatingWhatsapp = '98' . substr($floatingWhatsapp, 1);
        } elseif ($floatingWhatsapp !== '' && ! str_starts_with($floatingWhatsapp, '98')) {
            $floatingWhatsapp = '98' . $floatingWhatsapp;
        }
    @endphp
    @if(($storefrontPublicSettings['floating_contact_enabled'] ?? '0') === '1')
        <div class="fixed bottom-24 left-4 z-[75] flex flex-col gap-2 lg:bottom-6" aria-label="راه‌های ارتباط سریع">
            @if($floatingWhatsapp !== '')
                <a href="https://wa.me/{{ $floatingWhatsapp }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-emerald-700 px-4 py-3 text-sm font-bold text-white shadow-xl">واتساپ</a>
            @endif
            @if($floatingPhone !== '')
                <a href="tel:{{ $floatingPhone }}" class="rounded-full bg-slate-900 px-4 py-3 text-sm font-bold text-white shadow-xl">تماس با ما</a>
            @endif
            <a href="{{ route('contact') }}#contact-form" class="rounded-full border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-900 shadow-xl">پرسش</a>
        </div>
    @endif



</div>

@stack('scripts')

</body>
</html>
