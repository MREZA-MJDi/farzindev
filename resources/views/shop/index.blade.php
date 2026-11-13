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
        class="overflow-hidden bg-[var(--livora-cream)]"
        x-data="{
            filterOpen: false
        }"
    >

        {{-- Shop banner is configured in Admin > Storefront Settings. No looping product animation. --}}
        <section class="farzin-shop-banner border-b border-[var(--livora-border)] bg-[var(--livora-white)]">
            <x-layout.container>
                <div class="py-7 sm:py-9">
                    <nav aria-label="مسیر صفحه" class="farzin-shop-banner__breadcrumb flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]">
                        <a href="{{ route('home') }}" class="transition hover:text-[var(--livora-text)]">خانه</a>
                        <span aria-hidden="true">/</span>
                        <span aria-current="page" class="text-[var(--livora-text)]">فروشگاه</span>
                    </nav>

                    @if (!empty($shopBanner['image_url']))
                        <div class="farzin-shop-banner__image relative mt-6 aspect-[16/5] min-h-[220px] overflow-hidden rounded-[2rem] bg-[var(--livora-ink)]">
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
                                <p class="farzin-shop-banner__eyebrow text-[10px] font-medium uppercase tracking-[0.22em] text-white/70">فرجی‌هوم / فروشگاه</p>
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
                                <p class="farzin-shop-banner__eyebrow text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">فرجی‌هوم / فروشگاه</p>
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
                                    <span class="mr-2 text-[10px] opacity-65">{{ \App\Helpers\PersianNumberHelper::format($category->products_count) }}</span>
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
                                            راهنمای انتخاب
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
                                        :filterable-attributes="$filterableAttributes"
                                    />

                                </form>

                            </div>


                            {{-- =================================================
                                 DISCOVERY BOX
                            ================================================== --}}

                            <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-5">

                                <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                    فرجی‌هوم
                                </p>

                                <h3 class="mt-3 text-sm font-semibold">
                                    راهنمای انتخاب
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

                        <div class="rounded-3xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4 sm:p-5">

                            <div class="farzin-shop-toolbar">
                                <form action="{{ route('shop.index') }}" method="GET" role="search" aria-label="جستجوی محصولات فروشگاه" class="farzin-shop-search">
                                    @foreach(request()->except(['search', 'page']) as $filterName => $filterValue)
                                        @if(is_scalar($filterValue) && (string) $filterValue !== '')
                                            <input type="hidden" name="{{ $filterName }}" value="{{ $filterValue }}">
                                        @endif
                                    @endforeach
                                    <label for="shop-catalog-search" class="sr-only">نام محصول، مدل، کد یا مشخصات را جستجو کنید</label>
                                    <input id="shop-catalog-search" name="search" type="search" inputmode="search" enterkeyhint="search" autocomplete="off" value="{{ request('search', '') }}" placeholder="جستجوی هود، سینک، مدل یا کد محصول..." class="farzin-shop-search__input">
                                    <button type="submit" class="farzin-shop-search__submit">جستجو</button>
                                    @if(request()->filled('search'))
                                        <a href="{{ route('shop.index', request()->except(['search', 'page'])) }}" class="farzin-shop-search__clear">پاک کردن</a>
                                    @endif
                                </form>
                                <div class="farzin-shop-toolbar__sort">
                                    <x-shop.sorting :products="$products" />
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

                            <div class="farzin-shop-product-grid mt-8 grid grid-cols-2 gap-x-3 gap-y-6 sm:gap-x-4 sm:gap-y-8 md:grid-cols-3 2xl:grid-cols-4">

                                @foreach($products as $product)

                                    <x-product.card
                                        :product="$product"
                                        :reveal="false"
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
                                        :filterable-attributes="$filterableAttributes"
        />

    </div>

@endsection
