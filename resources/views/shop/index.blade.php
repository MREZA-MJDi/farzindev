@extends('layouts.app')

@section('title', 'فروشگاه هود و سینک | فرزین')

@section(
    'description',
    'خرید هود و سینک آشپزخانه از فرزین؛ مشخصات محصولات، مدل‌ها و گزینه‌های موجود را بررسی و مقایسه کنید.'
)

@section('canonical', route('shop.index'))

@push('seo')

    <meta
        property="og:type"
        content="website"
    >

    <meta
        property="og:title"
        content="فروشگاه هود و سینک | فرزین"
    >

    <meta
        property="og:description"
        content="مدل‌های هود و سینک فرزین را ببینید و گزینه متناسب با فضای نصب خود را پیدا کنید."
    >

    <meta
        property="og:url"
        content="{{ route('shop.index') }}"
    >

    <meta
        name="twitter:card"
        content="summary_large_image"
    >

    <meta
        name="twitter:title"
        content="فروشگاه هود و سینک | فرزین"
    >

    <meta
        name="twitter:description"
        content="کاتالوگ هود و سینک فرزین برای انتخابی متناسب با نیاز آشپزخانه."
    >

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "WebSite",
        "name": "فرزین",
        "url": @json(url('/')),
        "potentialAction": {
            "@@type": "SearchAction",
            "target": @json(url('/shop') . '?search={search_term_string}'),
            "query-input": "required name=search_term_string"
        }
    }
    </script>

@endpush


@section('content')

    <div
        class="overflow-hidden bg-[var(--livora-cream)]"
        x-data="{
            filterOpen: false
        }"
    >

        {{-- Shop banner is configured in Admin > Storefront Settings. No looping product animation. --}}
        <section class="farzin-shop-banner border-b border-[var(--livora-border)] bg-[var(--livora-white)]">
            <x-layout.container>
                <div class="py-7 sm:py-9">
                    <nav aria-label="مسیر صفحه" class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]">
                        <a href="{{ route('home') }}" class="transition hover:text-[var(--livora-ink)]">خانه</a>
                        <span aria-hidden="true">/</span>
                        <span aria-current="page" class="text-[var(--livora-ink)]">فروشگاه</span>
                    </nav>

                    @if (!empty($shopBanner['image_url']))
                        <div class="farzin-shop-banner__image relative mt-6 aspect-[16/5] min-h-[220px] overflow-hidden rounded-[2rem] bg-[var(--livora-ink)]" data-farzin-reveal>
                            <img
                                src="{{ $shopBanner['image_url'] }}"
                                alt="{{ $shopBanner['title'] }}"
                                class="absolute inset-0 h-full w-full object-cover"
                                width="1600"
                                height="500"
                                fetchpriority="high"
                                decoding="async"
                            >
                            <div class="absolute inset-0 bg-gradient-to-l from-black/75 via-black/25 to-transparent"></div>
                            <div class="farzin-shop-banner__content absolute inset-y-0 right-0 flex max-w-2xl flex-col justify-center p-6 text-white sm:p-10 lg:p-14">
                                <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-white/70">FARZIN / CATALOG</p>
                                <h1 class="mt-3 text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">{{ $shopBanner['title'] }}</h1>
                                @if (filled($shopBanner['subtitle']))
                                    <p class="mt-4 max-w-xl text-sm leading-8 text-white/80 sm:text-base">{{ $shopBanner['subtitle'] }}</p>
                                @endif
                                <a href="#shop-catalog" class="mt-6 inline-flex w-fit items-center rounded-xl border border-white/40 bg-white/10 px-5 py-3 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20">مشاهده کاتالوگ <span class="mr-2" aria-hidden="true">←</span></a>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between" data-farzin-reveal>
                            <div>
                                <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">FARZIN / CATALOG</p>
                                <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">فروشگاه فرزین</h1>
                                <p class="mt-3 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">{{ $shopBanner['subtitle'] }}</p>
                            </div>
                            <a href="#shop-catalog" class="inline-flex w-fit shrink-0 items-center rounded-xl bg-[var(--livora-ink)] px-5 py-3 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]">مشاهده محصولات <span class="mr-2" aria-hidden="true">←</span></a>
                        </div>
                    @endif
                </div>
            </x-layout.container>
        </section>


        {{-- =========================================================
             SHOP CONTENT
        ========================================================== --}}

        <section id="shop-catalog" class="farzin-shop-catalog">

            <x-layout.container>

                <div class="grid gap-8 py-8 lg:grid-cols-[250px_minmax(0,1fr)] lg:py-12">


                    {{-- =================================================
                         DESKTOP SIDEBAR
                    ================================================== --}}

                    <aside class="hidden min-w-0 lg:block">

                        <div class="sticky top-28 space-y-5">


                            {{-- =================================================
                                 FILTER CARD
                            ================================================== --}}

                            <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-5">

                                <div class="flex items-center justify-between gap-3">

                                    <div>

                                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                            DISCOVER
                                        </p>

                                        <h2 class="mt-2 text-sm font-semibold">
                                            فیلترها
                                        </h2>

                                    </div>


                                    <a
                                        href="{{ route('shop.index') }}"
                                        class="shrink-0 text-[11px] text-[var(--livora-stone)] transition hover:text-[var(--livora-accent)]"
                                    >
                                        پاک کردن
                                    </a>

                                </div>


                                {{-- Desktop Filter has its own GET form --}}

                                <form
                                    method="GET"
                                    action="{{ route('shop.index') }}"
                                    class="mt-6"
                                >

                                    <x-shop.filters
                                        :categories="$categories"
                                        :selected-category="request('category')"
                                    />

                                </form>

                            </div>


                            {{-- =================================================
                                 DISCOVERY BOX
                            ================================================== --}}

                            <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-5">

                                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    فرزین
                                </p>

                                <h3 class="mt-3 text-sm font-semibold">
                                    انتخاب آگاهانه
                                </h3>

                                <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                    قیمت، مشخصات فنی، موجودی و ویژگی‌های هر مدل را
                                    قبل از انتخاب محصول بررسی کنید.
                                </p>

                            </div>

                        </div>

                    </aside>


                    {{-- =================================================
                         PRODUCTS AREA
                    ================================================== --}}

                    <div class="min-w-0">


                        {{-- =================================================
                             MOBILE TOOLBAR
                        ================================================== --}}

                        <div class="mb-5 flex items-center justify-between gap-3 lg:hidden">

                            <button
                                type="button"
                                @click="filterOpen = true"
                                class="inline-flex items-center gap-2 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3 text-sm font-medium text-[var(--livora-ink)] transition hover:border-[var(--livora-ink)]"
                            >

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-4 w-4"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3.75 6.75h16.5M6.75 12h10.5m-7.5 5.25h5.25"
                                    />
                                </svg>

                                فیلترها

                            </button>


                            <span class="text-xs text-[var(--livora-stone)]">

                                <span class="font-medium text-[var(--livora-ink)]">
                                    {{ \App\Helpers\PersianNumberHelper::format($products->total()) }}
                                </span>

                                محصول

                            </span>

                        </div>


                        {{-- =================================================
                             SORTING TOOLBAR
                        ================================================== --}}

                        <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4 sm:p-5">

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                <div class="min-w-0">

                                    <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                        CURATED PRODUCTS
                                    </p>

                                    <p class="mt-2 text-sm font-medium text-[var(--livora-ink)]">
                                        مجموعه فرزین
                                    </p>

                                </div>


                                <div class="w-full sm:min-w-[220px] sm:w-auto">

                                    <x-shop.sorting
                                        :products="$products"
                                    />

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                             ACTIVE FILTER
                        ================================================== --}}

                        @if(request()->filled('category'))

                            @php
                                $activeCategory = $categories->firstWhere(
                                    'slug',
                                    request('category')
                                );
                            @endphp


                            <div class="mt-5 flex flex-wrap items-center gap-2">

                                <span class="text-xs text-[var(--livora-stone)]">
                                    فیلتر فعال:
                                </span>

                                <span class="inline-flex items-center rounded-full border border-[var(--livora-border)] bg-[var(--livora-white)] px-3 py-1.5 text-[11px] text-[var(--livora-ink)]">
                                    {{ $activeCategory?->name ?? request('category') }}
                                </span>

                                <a
                                    href="{{ route('shop.index') }}"
                                    class="inline-flex items-center rounded-full border border-[var(--livora-border)] px-3 py-1.5 text-[11px] text-[var(--livora-stone)] transition hover:border-[var(--livora-ink)] hover:text-[var(--livora-ink)]"
                                >
                                    حذف
                                </a>

                            </div>

                        @endif


                        {{-- =================================================
                             RESULT META
                        ================================================== --}}

                        @if($products->total() > 0)

                            <div class="mt-6">

                                <p class="text-xs text-[var(--livora-stone)]">

                                    نمایش

                                    <span class="font-medium text-[var(--livora-ink)]">
                                        {{ \App\Helpers\PersianNumberHelper::format($products->firstItem()) }}
                                    </span>

                                    تا

                                    <span class="font-medium text-[var(--livora-ink)]">
                                        {{ \App\Helpers\PersianNumberHelper::format($products->lastItem()) }}
                                    </span>

                                    از

                                    <span class="font-medium text-[var(--livora-ink)]">
                                        {{ \App\Helpers\PersianNumberHelper::format($products->total()) }}
                                    </span>

                                </p>

                            </div>

                        @endif


                        {{-- =================================================
                             PRODUCT GRID
                        ================================================== --}}

                        @if($products->isNotEmpty())

                            <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-5 lg:grid-cols-2 xl:grid-cols-3">

                                @foreach($products as $product)

                                    <x-product.card
                                        :product="$product"
                                    />

                                @endforeach

                            </div>


                            {{-- =================================================
                                 PAGINATION
                            ================================================== --}}

                            @if($products->hasPages())

                                <x-ui.pagination :paginator="$products" />

                            @endif


                        @else

                            {{-- =================================================
                                 EMPTY STATE
                            ================================================== --}}

                            <div class="mt-8 rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] px-6 py-24 text-center">

                                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-sm text-[var(--livora-stone)]">
                                    —
                                </span>


                                <h2 class="mt-6 text-2xl font-semibold text-[var(--livora-ink)]">
                                    محصولی پیدا نشد
                                </h2>


                                <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-[var(--livora-stone)]">
                                    فیلترها یا عبارت جستجو را تغییر دهید و دوباره مجموعه محصولات را بررسی کنید.
                                </p>


                                <a
                                    href="{{ route('shop.index') }}"
                                    class="mt-7 inline-flex rounded-2xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                                >
                                    مشاهده همه محصولات
                                </a>

                            </div>

                        @endif

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             MOBILE FILTER DRAWER
        ========================================================== --}}

        <x-shop.filter-drawer
            :categories="$categories"
            :selected-category="request('category')"
        />

    </div>

@endsection
