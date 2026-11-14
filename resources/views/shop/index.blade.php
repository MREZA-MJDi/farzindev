@extends('layouts.app')

@section('title', 'فروشگاه تجهیزات آشپزخانه | فرجی‌هوم')

@section(
    'description',
    'خرید هود، اجاق گاز صفحه‌ای و سینک آشپزخانه؛ مشخصات فنی، قیمت و موجودی مدل‌ها را در فروشگاه فرجی‌هوم بررسی و مقایسه کنید.'
)

@section('canonical', route('shop.index'))

@php
    $shopBannerTitle = str_ireplace(['فرزین فرجی', 'فرزین', 'Farzin', 'FARZIN'], ['فرجی‌هوم', 'فرجی‌هوم', 'فرجی‌هوم', 'فرجی‌هوم'], (string) ($shopBanner['title'] ?? 'فروشگاه فرجی‌هوم'));
    $shopBannerSubtitle = str_ireplace(['فرزین فرجی', 'فرزین', 'Farzin', 'FARZIN'], ['فرجی‌هوم', 'فرجی‌هوم', 'فرجی‌هوم', 'فرجی‌هوم'], (string) ($shopBanner['subtitle'] ?? 'محصولات تجهیزات آشپزخانه را مقایسه کنید.'));
@endphp




@section('content')

    <div
        class="min-w-0 bg-[var(--livora-cream)]"
        x-data="{ filterOpen: false }"
        x-effect="document.body.style.overflow = filterOpen ? 'hidden' : ''"
        @keydown.escape.window="filterOpen = false"
    >

        {{-- Shop banner is configured in Admin > Storefront Settings. No looping product animation. --}}
        <section class="farzin-shop-banner border-b border-[var(--livora-border)] bg-[var(--livora-white)]">
            <x-layout.container>
                <div class="py-5 sm:py-6">
                    <nav aria-label="مسیر صفحه" class="farzin-shop-banner__breadcrumb flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]">
                        <a href="{{ route('home') }}" class="transition hover:text-[var(--livora-text)]">خانه</a>
                        <span aria-hidden="true">/</span>
                        <span aria-current="page" class="text-[var(--livora-text)]">فروشگاه</span>
                    </nav>

                    @if (!empty($shopBanner['image_url']))
                        <div class="farzin-shop-banner__image relative mt-4 aspect-[16/5] min-h-[220px] overflow-hidden bg-[var(--livora-ink)]">
                            <img
                                src="{{ $shopBanner['image_url'] }}"
                                @if(!empty($shopBanner['image_srcset'])) srcset="{{ $shopBanner['image_srcset'] }}" sizes="100vw" @endif
                                alt="{{ $shopBannerTitle }}"
                                class="absolute inset-0 h-full w-full object-cover"
                                width="1600"
                                height="500"
                                fetchpriority="high"
                                decoding="async"
                            >
                            <div class="absolute inset-0 bg-gradient-to-l from-black/75 via-black/25 to-transparent"></div>
                            <div class="farzin-shop-banner__content absolute inset-y-0 right-0 flex max-w-2xl flex-col justify-center p-6 text-white sm:p-10 lg:p-14">
                                <p class="farzin-shop-banner__eyebrow text-[10px] font-medium uppercase tracking-[0.22em] text-white/70">کاتالوگ محصولات</p>
                                <h1 class="farzin-shop-banner__title mt-3 text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">{{ $shopBannerTitle }}</h1>
                                @if (filled($shopBanner['subtitle']))
                                    <p class="farzin-shop-banner__description mt-4 max-w-xl text-sm leading-8 text-white/80 sm:text-base">{{ $shopBannerSubtitle }}</p>
                                @endif
                                <a href="#shop-catalog" class="farzin-shop-banner__action mt-6 inline-flex min-h-11 w-fit items-center gap-2 rounded-xl border border-white/40 bg-white/10 px-5 py-3 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/20"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0" aria-hidden="true"><rect x="3.5" y="3.5" width="7" height="7" rx="1.3" /><rect x="13.5" y="3.5" width="7" height="7" rx="1.3" /><rect x="3.5" y="13.5" width="7" height="7" rx="1.3" /><rect x="13.5" y="13.5" width="7" height="7" rx="1.3" /></svg> مشاهده محصولات</a>
                            </div>
                        </div>
                    @else
                        <div class="mt-6 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="farzin-shop-banner__eyebrow text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">کاتالوگ محصولات</p>
                                <h1 class="farzin-shop-banner__title mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">{{ $shopBannerTitle }}</h1>
                                <p class="farzin-shop-banner__description mt-3 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">{{ $shopBannerSubtitle }}</p>
                            </div>
                            <a href="#shop-catalog" class="farzin-shop-banner__action inline-flex min-h-11 w-fit shrink-0 items-center gap-2 rounded-xl bg-[var(--livora-ink)] px-5 py-3 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0" aria-hidden="true"><path d="M3.5 9h17l-1 11h-15l-1-11Z" /><path d="M8 9V6.5a4 4 0 0 1 8 0V9" /></svg> مشاهده محصولات</a>
                        </div>
                    @endif
                </div>
            </x-layout.container>
        </section>


        @if($categories->isNotEmpty())
            <nav aria-label="دسترسی سریع به دسته‌بندی‌ها" class="border-b border-[var(--livora-border)] bg-white">
                <x-layout.container>
                    <div class="flex flex-col gap-3 py-5 sm:flex-row sm:items-center sm:gap-6">
                        <span class="shrink-0 text-xs font-bold text-[var(--livora-ink)]">انتخاب سریع دسته:</span>
                        <div class="flex min-w-0 flex-wrap gap-2">
                            @foreach($categories as $category)
                                <a href="{{ route('shop.index', ['category' => $category->slug]) }}" @class(['inline-flex min-h-10 items-center border px-4 py-2 text-xs font-semibold transition', 'border-[var(--livora-accent)] bg-[var(--livora-accent)] text-white' => request('category') === $category->slug, 'border-[var(--livora-border)] bg-[var(--livora-cream)] text-[var(--livora-text)] hover:border-[var(--livora-accent)] hover:text-[var(--livora-accent)]' => request('category') !== $category->slug])>
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </x-layout.container>
            </nav>
        @endif

        {{-- =========================================================
             SHOP CONTENT
        ========================================================== --}}

        <section id="shop-catalog" class="farzin-shop-catalog">

            <x-layout.container>

                <div class="farzin-shop-layout grid items-start gap-5 py-6 lg:grid-cols-[270px_minmax(0,1fr)] lg:gap-7 lg:py-8">


                    {{-- One responsive filter rail: desktop sidebar and mobile drawer share the same form. --}}
                    <button
                        type="button"
                        class="farzin-shop-filter-backdrop"
                        aria-label="بستن فیلترها"
                        @click="filterOpen = false"
                        :class="{ 'is-open': filterOpen }"
                    ></button>

                    <aside
                        id="shop-filter-rail"
                        class="farzin-shop-filter-rail min-w-0 self-start"
                        :class="{ 'is-open': filterOpen }"
                        aria-label="فیلتر محصولات"
                    >
                        <div class="farzin-shop-filter-rail__panel">
                            <div class="farzin-shop-filter-rail__header">
                                <div>
                                    <p class="farzin-shop-filter-rail__eyebrow">راهنمای انتخاب</p>
                                    <h2>فیلتر محصولات</h2>
                                </div>
                                <button
                                    type="button"
                                    class="farzin-shop-filter-rail__close"
                                    aria-label="بستن فیلترها"
                                    @click="filterOpen = false"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 6 12 12M18 6 6 18"/>
                                    </svg>
                                </button>
                            </div>

                            <form method="GET" action="{{ route('shop.index') }}" class="farzin-shop-filter-form">
                                <x-shop.filters
                                    :categories="$categories"
                                    :selected-category="request('category')"
                                    :filterable-attributes="$filterableAttributes"
                                />
                            </form>
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
                                aria-controls="shop-filter-rail"
                                 :aria-expanded="filterOpen.toString()"
                                class="inline-flex items-center gap-2 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3 text-sm font-medium text-[var(--livora-text)] transition hover:border-[var(--livora-ink)]"
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

                                <span class="font-medium text-[var(--livora-text)]">
                                    {{ \App\Helpers\PersianNumberHelper::format($products->total()) }}
                                </span>

                                محصول

                            </span>

                        </div>


                        {{-- =================================================
                             SORTING TOOLBAR
                        ================================================== --}}

                        {{-- Catalogue search, independent from the global header search. --}}
                        <form method="GET" action="{{ route('shop.index') }}" role="search" class="farzin-shop-searchbar mb-5">
                            @foreach(request()->except(['search', 'page']) as $key => $value)
                                @if(is_array($value))
                                    @foreach($value as $nestedKey => $nestedValue)
                                        @if(is_array($nestedValue))
                                            @foreach($nestedValue as $deepKey => $deepValue)
                                                <input type="hidden" name="{{ $key }}[{{ $nestedKey }}][{{ $deepKey }}]" value="{{ $deepValue }}">
                                            @endforeach
                                        @else
                                            <input type="hidden" name="{{ $key }}[{{ $nestedKey }}]" value="{{ $nestedValue }}">
                                        @endif
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label class="sr-only" for="shop-catalog-search">جست‌وجوی محصولات فروشگاه</label>
                            <div class="farzin-shop-searchbar__field">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <circle cx="10.8" cy="10.8" r="6.8"></circle>
                                    <path stroke-linecap="round" d="m16 16 4.5 4.5"></path>
                                </svg>
                                <input id="shop-catalog-search" type="search" name="search" value="{{ request('search') }}" maxlength="150" placeholder="جست‌وجوی نام محصول، برند یا کد کالا..." autocomplete="off">
                                @if(request()->filled('search'))
                                    <a href="{{ route('shop.index', request()->except(['search', 'page'])) }}" class="farzin-shop-searchbar__clear" aria-label="پاک کردن جست‌وجو">پاک کردن</a>
                                @endif
                            </div>
                            <button type="submit" class="farzin-shop-searchbar__submit">جست‌وجو</button>
                        </form>

                        <div class="farzin-shop-sortbar flex items-center justify-between gap-4 border-b border-[var(--livora-border)] pb-4">
                            <div class="farzin-shop-toolbar__sort min-w-0">
                                <x-shop.sorting :products="$products" />
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

                                <span class="inline-flex items-center rounded-full border border-[var(--livora-border)] bg-[var(--livora-white)] px-3 py-1.5 text-[11px] text-[var(--livora-text)]">
                                    {{ $activeCategory?->name ?? request('category') }}
                                </span>

                                <a
                                    href="{{ route('shop.index') }}"
                                    class="inline-flex items-center rounded-full border border-[var(--livora-border)] px-3 py-1.5 text-[11px] text-[var(--livora-stone)] transition hover:border-[var(--livora-ink)] hover:text-[var(--livora-text)]"
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

                                    <span class="font-medium text-[var(--livora-text)]">
                                        {{ \App\Helpers\PersianNumberHelper::format($products->firstItem()) }}
                                    </span>

                                    تا

                                    <span class="font-medium text-[var(--livora-text)]">
                                        {{ \App\Helpers\PersianNumberHelper::format($products->lastItem()) }}
                                    </span>

                                    از

                                    <span class="font-medium text-[var(--livora-text)]">
                                        {{ \App\Helpers\PersianNumberHelper::format($products->total()) }}
                                    </span>

                                </p>

                            </div>

                        @endif


                        {{-- =================================================
                             PRODUCT GRID
                        ================================================== --}}

                        @if($products->isNotEmpty())

                            <div class="farzin-shop-product-grid mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

                                @foreach($products as $product)

                                    <x-product.card
                                        :product="$product"
                                        :reveal="false"
                                        variant="catalog"
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


                                <h2 class="mt-6 text-2xl font-semibold text-[var(--livora-text)]">
                                    محصولی پیدا نشد
                                </h2>


                                <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-[var(--livora-stone)]">
                                    فیلترها یا عبارت جستجو را تغییر دهید و دوباره مجموعه محصولات را بررسی کنید.
                                </p>


                                <a
                                    href="{{ route('shop.index') }}"
                                    class="mt-7 inline-flex rounded-2xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                                >
                                    پاک کردن فیلترها
                                </a>

                            </div>

                        @endif

                    </div>

                </div>

            </x-layout.container>

        </section>



    </div>

@endsection
