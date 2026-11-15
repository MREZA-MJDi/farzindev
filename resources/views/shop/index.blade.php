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

        <section class="shop-reference-heading">
            <x-layout.container>
                <nav aria-label="مسیر صفحه" class="shop-reference-heading__breadcrumb">
                    <a href="{{ route('home') }}">خانه</a><span aria-hidden="true">/</span><span aria-current="page">فروشگاه</span>
                </nav>
                <div class="shop-reference-heading__title">
                    <span class="shop-reference-heading__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7 6 4h12l2 3M4 7h16v13H4zM9 12h6"/></svg>
                    </span>
                    <h1>فروشگاه</h1>
                </div>
            </x-layout.container>
        </section>



        @if($categories->isNotEmpty())
            <section class="shop-reference-categories" aria-label="دسته‌بندی محصولات">
                <x-layout.container>
                    <div class="shop-reference-categories__grid">
                        @foreach($categories as $category)
                            <a href="{{ route('shop.index', ['category' => $category->slug]) }}"
                               @class(['shop-reference-category', 'is-active' => request('category') === $category->slug])
                               aria-current="{{ request('category') === $category->slug ? 'true' : 'false' }}">
                                <span class="shop-reference-category__media">
                                    @if($category->image_url)
                                        <img src="{{ $category->image_url }}" @if($category->image_srcset) srcset="{{ $category->image_srcset }}" sizes="(max-width: 600px) 42vw, 150px" @endif alt="" loading="lazy" decoding="async">
                                    @else
                                        <svg viewBox="0 0 64 64" fill="none" aria-hidden="true">
                                            <rect x="7" y="18" width="50" height="34" rx="6" fill="#dfe2f2" stroke="#6d5ae0" stroke-width="2"/>
                                            <path d="M16 18V12h32v6M15 34h34M23 34v10m18-10v10" stroke="#6d5ae0" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    @endif
                                </span>
                                <strong>{{ $category->name }}</strong>
                                <small>{{ \App\Helpers\PersianNumberHelper::format($category->products_count ?? 0) }} محصول</small>
                            </a>
                        @endforeach
                    </div>
                </x-layout.container>
            </section>
        @endif

        {{-- =========================================================
             SHOP CONTENT
        ========================================================== --}}

        <section id="shop-catalog" class="farzin-shop-catalog">

            <x-layout.container>

                <div class="farzin-shop-layout grid items-start gap-5 py-6 lg:grid-cols-[270px_minmax(0,1fr)] lg:gap-7 lg:py-8">


                    {{-- =================================================
                         DESKTOP SIDEBAR
                    ================================================== --}}

                    <aside class="hidden min-w-0 self-start lg:block">

                        <div class="space-y-4">


                            {{-- =================================================
                                 FILTER CARD
                            ================================================== --}}

                            <div class="max-h-[calc(100vh-120px)] max-h-[calc(100dvh-120px)] overflow-y-auto overscroll-contain rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-3 [scrollbar-width:thin] sm:p-4">

                                <div class="flex items-center justify-between gap-3">

                                    <div>

                                        <p class="text-[10px] uppercase tracking-[0.18em] text-[var(--livora-accent)]">
                                            راهنمای انتخاب
                                        </p>

                                        <h2 class="mt-1 text-sm font-semibold">
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
                                    class="mt-4"
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

                            <div class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-surface)] p-4">

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
                            <div>
                                <p class="text-[10px] font-semibold tracking-wide text-[var(--livora-accent)]">کاتالوگ فرجی‌هوم</p>
                                <p class="mt-1 text-xs text-[var(--livora-stone)]">مرتب‌سازی محصولات</p>
                            </div>
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
