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

        {{-- Cinematic catalog intro: editorial type + engineered product artwork --}}
        <section class="farzin-shop-hero relative overflow-hidden" data-farzin-shop-hero aria-labelledby="shop-hero-title">
            <div class="farzin-shop-hero__grid" aria-hidden="true"></div>
            <div class="farzin-shop-hero__glow farzin-shop-hero__glow--one" aria-hidden="true"></div>
            <div class="farzin-shop-hero__glow farzin-shop-hero__glow--two" aria-hidden="true"></div>

            <x-layout.container>
                <div class="farzin-shop-hero__layout">
                    <div class="farzin-shop-hero__copy">
                        <nav aria-label="مسیر صفحه" class="farzin-shop-hero__breadcrumb" data-shop-animate>
                            <a href="{{ route('home') }}">خانه</a>
                            <span aria-hidden="true">/</span>
                            <span aria-current="page">فروشگاه</span>
                        </nav>

                        <p class="farzin-shop-hero__eyebrow" data-shop-animate>
                            <span class="farzin-shop-hero__eyebrow-dot" aria-hidden="true"></span>
                            FARZIN <span aria-hidden="true">/</span> KITCHEN SYSTEMS
                        </p>

                        <h1 id="shop-hero-title" class="farzin-shop-hero__title" data-shop-animate>
                            جزئیات، تفاوت را <span>می‌سازند.</span>
                        </h1>

                        <p class="farzin-shop-hero__description" data-shop-animate>
                            هود و سینک فرزین را با نگاهی دقیق‌تر ببینید؛ از فرم و متریال تا مشخصات کاربردی هر مدل.
                        </p>

                        <div class="farzin-shop-hero__actions" data-shop-animate>
                            <a href="#shop-catalog" class="farzin-shop-hero__button farzin-shop-hero__button--primary">
                                <span>ورود به کاتالوگ</span>
                                <span class="farzin-shop-hero__button-arrow" aria-hidden="true">↙</span>
                            </a>
                            <a href="{{ route('categories.index') }}" class="farzin-shop-hero__button farzin-shop-hero__button--secondary">
                                دسته‌بندی‌ها <span aria-hidden="true">←</span>
                            </a>
                        </div>

                        <div class="farzin-shop-hero__meta" data-shop-animate>
                            <span><b>۰۱</b> هود آشپزخانه</span>
                            <i aria-hidden="true"></i>
                            <span><b>۰۲</b> سینک ظرفشویی</span>
                            <i aria-hidden="true"></i>
                            <span class="farzin-shop-hero__count">{{ number_format($products->total()) }} محصول</span>
                        </div>
                    </div>

                    <div class="farzin-shop-hero__art" data-shop-art aria-label="تصویر گرافیکی محصولات هود و سینک فرزین" role="img">
                        <div class="farzin-shop-hero__art-orbit farzin-shop-hero__art-orbit--outer" aria-hidden="true"></div>
                        <div class="farzin-shop-hero__art-orbit farzin-shop-hero__art-orbit--inner" aria-hidden="true"></div>
                        <div class="farzin-shop-hero__art-index" aria-hidden="true"><span>FZ</span><i></i><span>01—02</span></div>

                        <svg class="farzin-shop-hero__hood" viewBox="0 0 620 430" fill="none" aria-hidden="true">
                            <defs>
                                <linearGradient id="farzinSteelFace" x1="132" y1="170" x2="470" y2="270" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#F7FAFA"/>
                                    <stop offset=".22" stop-color="#BEC8CB"/>
                                    <stop offset=".47" stop-color="#EDF1F2"/>
                                    <stop offset=".72" stop-color="#9BA7AB"/>
                                    <stop offset="1" stop-color="#E1E7E9"/>
                                </linearGradient>
                                <linearGradient id="farzinSteelSide" x1="452" y1="170" x2="510" y2="244" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#E7ECEE"/>
                                    <stop offset="1" stop-color="#5D696E"/>
                                </linearGradient>
                                <linearGradient id="farzinSteelChimney" x1="260" y1="52" x2="394" y2="158" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#F7FAFA"/>
                                    <stop offset=".5" stop-color="#B9C3C6"/>
                                    <stop offset="1" stop-color="#7C898E"/>
                                </linearGradient>
                                <filter id="farzinHoodShadow" x="56" y="98" width="526" height="290" filterUnits="userSpaceOnUse">
                                    <feGaussianBlur stdDeviation="18"/>
                                </filter>
                            </defs>
                            <ellipse cx="317" cy="337" rx="206" ry="26" fill="#020405" fill-opacity=".46" filter="url(#farzinHoodShadow)"/>
                            <g class="farzin-shop-hero__hood-body">
                                <path d="M259 45H385V170H259V45Z" fill="url(#farzinSteelChimney)" stroke="#F6FAFA" stroke-opacity=".65"/>
                                <path d="M274 57H368V167H274V57Z" fill="#849196" fill-opacity=".22"/>
                                <path d="M165 174H454L493 225H124L165 174Z" fill="url(#farzinSteelFace)" stroke="#F8FBFB" stroke-opacity=".8"/>
                                <path d="M454 174L493 225H467L428 174H454Z" fill="url(#farzinSteelSide)"/>
                                <path d="M124 225H493L470 250H146L124 225Z" fill="#647176"/>
                                <path d="M144 229H473" stroke="#F4F8F9" stroke-opacity=".76" stroke-width="2"/>
                                <path d="M178 237H439" stroke="#20292D" stroke-opacity=".55" stroke-width="4" stroke-linecap="round"/>
                                <path d="M183 245H435" stroke="#20292D" stroke-opacity=".28" stroke-width="2" stroke-linecap="round"/>
                                <rect x="288" y="190" width="50" height="14" rx="7" fill="#4D595E"/>
                                <circle cx="299" cy="197" r="2" fill="#BCE8E9"/>
                                <circle cx="312" cy="197" r="2" fill="#DDE5E6"/>
                                <circle cx="325" cy="197" r="2" fill="#DDE5E6"/>
                                <path d="M270 76H374" stroke="#FFFFFF" stroke-opacity=".46" stroke-width="2"/>
                            </g>
                            <path d="M108 285H502" stroke="#8ED5D8" stroke-opacity=".38" stroke-dasharray="3 9"/>
                            <circle cx="109" cy="285" r="4" fill="#8ED5D8"/>
                            <circle cx="501" cy="285" r="4" fill="#8ED5D8"/>
                            <text x="105" y="309" fill="#E4EAEC" fill-opacity=".65" font-size="10" letter-spacing="3">FORM / FUNCTION</text>
                        </svg>

                        <div class="farzin-shop-hero__sink-card" aria-hidden="true">
                            <div class="farzin-shop-hero__sink-card-label"><span>02</span><span>SINK / SERIES</span></div>
                            <svg viewBox="0 0 240 130" fill="none">
                                <defs>
                                    <linearGradient id="farzinSinkSteel" x1="35" y1="28" x2="195" y2="112" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#F8FBFB"/>
                                        <stop offset=".45" stop-color="#9EAAAE"/>
                                        <stop offset=".73" stop-color="#E9EEEF"/>
                                        <stop offset="1" stop-color="#77858A"/>
                                    </linearGradient>
                                </defs>
                                <path d="M35 28H205L221 45L204 103Q202 109 195 109H48Q41 109 39 102L20 45L35 28Z" fill="url(#farzinSinkSteel)" stroke="#F9FCFC" stroke-opacity=".75"/>
                                <path d="M48 43H191L202 93Q202 98 196 98H49Q44 98 43 93L32 43H48Z" fill="#303A3E" stroke="#D8E1E3" stroke-opacity=".8" stroke-width="2"/>
                                <ellipse cx="122" cy="82" rx="12" ry="7" fill="#13191C" stroke="#B8C5C8" stroke-width="2"/>
                                <path d="M91 38V24Q91 12 104 12H125Q138 12 138 25V32" stroke="#E5EBED" stroke-width="7" stroke-linecap="round"/>
                                <path d="M91 38V24Q91 12 104 12H125Q138 12 138 25V32" stroke="#7D8A8F" stroke-width="2" stroke-linecap="round"/>
                                <path d="M30 47H209" stroke="#FFFFFF" stroke-opacity=".8" stroke-width="2"/>
                            </svg>
                            <div class="farzin-shop-hero__sink-card-footer"><span>STAINLESS STEEL</span><span>↗</span></div>
                        </div>

                        <div class="farzin-shop-hero__art-note" aria-hidden="true">
                            <span class="farzin-shop-hero__art-note-line"></span>
                            <span>ENGINEERED FOR EVERYDAY</span>
                        </div>
                    </div>
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
                                    {{ number_format($products->total()) }}
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
                                        {{ number_format($products->firstItem()) }}
                                    </span>

                                    تا

                                    <span class="font-medium text-[var(--livora-ink)]">
                                        {{ number_format($products->lastItem()) }}
                                    </span>

                                    از

                                    <span class="font-medium text-[var(--livora-ink)]">
                                        {{ number_format($products->total()) }}
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

                                <nav
                                    class="mt-14"
                                    aria-label="صفحه‌بندی محصولات"
                                >

                                    {{ $products->withQueryString()->links() }}

                                </nav>

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
