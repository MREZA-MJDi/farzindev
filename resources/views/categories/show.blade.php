@extends('layouts.app')

@php
    $categoryGuides = [
        'hoods' => [
            'eyebrow' => 'راهنمای انتخاب هود',
            'title' => 'هودی متناسب با چیدمان و سبک آشپزی شما',
            'intro' => 'برای انتخاب هود، ابتدا نوع نصب و عرض فضای بالای اجاق را مشخص کنید. سپس قدرت مکش، سطح صدا، تعداد دور موتور، نوع فیلتر و روشنایی را در مشخصات هر مدل مقایسه کنید.',
            'meta' => 'مدل‌های هود آشپزخانه را بر اساس نوع نصب، ابعاد، قدرت مکش، سطح صدا و امکانات فنی مقایسه کنید.',
            'factors' => [
                ['title' => 'نوع نصب و فرم', 'body' => 'مخفی، مورب، شومینه‌ای یا زیرکابینتی؛ نوع نصب باید با طراحی کابینت و فضای موجود هماهنگ باشد.'],
                ['title' => 'ابعاد و عرض', 'body' => 'عرض هود و فاصله نصب را با ابعاد اجاق و دستورالعمل نصب همان مدل تطبیق دهید.'],
                ['title' => 'عملکرد و نگهداری', 'body' => 'قدرت مکش، میزان صدا، نوع فیلتر، دسترسی برای نظافت و روشنایی را پیش از انتخاب بررسی کنید.'],
            ],
            'note' => 'اعداد و امکانات از یک مدل به مدل دیگر متفاوت‌اند؛ مشخصات ثبت‌شده در صفحه همان محصول را ملاک قرار دهید.',
        ],
        'sinks' => [
            'eyebrow' => 'راهنمای انتخاب سینک',
            'title' => 'سینکی هماهنگ با فضای کابینت و شیوه استفاده شما',
            'intro' => 'پیش از انتخاب سینک، ابعاد کابینت و برش صفحه را اندازه بگیرید. سپس روش نصب، تعداد و عمق لگن، جهت سینی و جنس محصول را با نیاز روزمره و فضای آشپزخانه تطبیق دهید.',
            'meta' => 'انواع سینک ظرفشویی را با توجه به روش نصب، ابعاد، تعداد لگن، عمق لگن و جنس محصول مقایسه کنید.',
            'factors' => [
                ['title' => 'روش نصب', 'body' => 'توکار، روکار یا زیرکار بودن سینک روی اندازه برش، شیوه نصب و ظاهر نهایی صفحه کابینت اثر دارد.'],
                ['title' => 'تعداد و عمق لگن', 'body' => 'با توجه به حجم شست‌وشو و فضای کار، تعداد لگن و عمق مناسب را انتخاب کنید.'],
                ['title' => 'جنس و نگهداری', 'body' => 'جنس، پوشش سطح، مقاومت در برابر خط‌وخش و روش نظافت را از مشخصات اختصاصی همان مدل بررسی کنید.'],
            ],
            'note' => 'پیش از سفارش، ابعاد بیرونی و ابعاد برش موردنیاز را با نقشه نصب محصول تطبیق دهید.',
        ],
        'built-in-stove' => [
            'eyebrow' => 'راهنمای انتخاب اجاق گاز صفحه‌ای',
            'title' => 'اجاق گاز مناسب؛ با توجه به ابعاد، شعله‌ها و ایمنی',
            'intro' => 'برای انتخاب اجاق گاز صفحه‌ای، ابتدا فضای نصب و ابعاد برش صفحه کابینت را اندازه بگیرید. سپس تعداد و چیدمان شعله‌ها، جنس صفحه، شبکه‌ها، نوع کنترل و امکانات ایمنی هر مدل را مقایسه کنید.',
            'meta' => 'اجاق گاز صفحه‌ای را با توجه به ابعاد نصب، تعداد شعله، جنس صفحه، شبکه و امکانات ایمنی مقایسه کنید.',
            'factors' => [
                ['title' => 'ابعاد و برش صفحه', 'body' => 'ابعاد کلی محصول با اندازه برش کابینت یکسان نیست؛ هر دو اندازه را از نقشه فنی مدل بررسی کنید.'],
                ['title' => 'تعداد و چیدمان شعله', 'body' => 'تعداد شعله‌ها و فاصله آن‌ها را با اندازه ظروف و شیوه آشپزی روزانه خود تطبیق دهید.'],
                ['title' => 'ایمنی و نظافت', 'body' => 'نوع شبکه، جنس صفحه، سیستم جرقه‌زن و وجود ترموکوپل یا سایر امکانات ایمنی را در مشخصات همان مدل بخوانید.'],
            ],
            'note' => 'نصب تجهیزات گازسوز باید مطابق دستورالعمل سازنده و توسط فرد متخصص انجام شود.',
        ],
        'gas-hobs' => [
            'eyebrow' => 'راهنمای انتخاب اجاق گاز صفحه‌ای',
            'title' => 'اجاق گاز مناسب؛ با توجه به ابعاد، شعله‌ها و ایمنی',
            'intro' => 'برای انتخاب اجاق گاز صفحه‌ای، ابتدا فضای نصب و ابعاد برش صفحه کابینت را اندازه بگیرید. سپس تعداد و چیدمان شعله‌ها، جنس صفحه، شبکه‌ها، نوع کنترل و امکانات ایمنی هر مدل را مقایسه کنید.',
            'meta' => 'اجاق گاز صفحه‌ای را با توجه به ابعاد نصب، تعداد شعله، جنس صفحه، شبکه و امکانات ایمنی مقایسه کنید.',
            'factors' => [
                ['title' => 'ابعاد و برش صفحه', 'body' => 'ابعاد کلی محصول با اندازه برش کابینت یکسان نیست؛ هر دو اندازه را از نقشه فنی مدل بررسی کنید.'],
                ['title' => 'تعداد و چیدمان شعله', 'body' => 'تعداد شعله‌ها و فاصله آن‌ها را با اندازه ظروف و شیوه آشپزی روزانه خود تطبیق دهید.'],
                ['title' => 'ایمنی و نظافت', 'body' => 'نوع شبکه، جنس صفحه، سیستم جرقه‌زن و وجود ترموکوپل یا سایر امکانات ایمنی را در مشخصات همان مدل بخوانید.'],
            ],
            'note' => 'نصب تجهیزات گازسوز باید مطابق دستورالعمل سازنده و توسط فرد متخصص انجام شود.',
        ],
    ];

    foreach (['built-in-gas', 'built-in-hobs', 'gas-stove', 'gas-stoves', 'stoves'] as $gasSlug) {
        $categoryGuides[$gasSlug] = $categoryGuides['built-in-stove'];
    }

    $categoryGuide = $categoryGuides[$category->slug] ?? null;
    $categoryNameLower = mb_strtolower($category->name);
    if (! $categoryGuide && (str_contains($categoryNameLower, 'اجاق') || str_contains($categoryNameLower, 'گاز'))) {
        $categoryGuide = $categoryGuides['built-in-stove'];
    } elseif (! $categoryGuide && str_contains($categoryNameLower, 'هود')) {
        $categoryGuide = $categoryGuides['hoods'];
    } elseif (! $categoryGuide && str_contains($categoryNameLower, 'سینک')) {
        $categoryGuide = $categoryGuides['sinks'];
    }
    $normalizeCategoryCopy = static fn (?string $value): string => str_ireplace(
        ['فرزین فرجی', 'فرزین', 'FARZIN', 'Farzin'],
        ['فرجی‌هوم', 'فرجی‌هوم', 'FARAJI HOME', 'Faraji Home'],
        trim((string) $value)
    );

    $categoryPageTitle = $category->meta_title ?: (($categoryGuide['title'] ?? $category->name) . ' | فرجی‌هوم');
    $categoryPageTitle = $normalizeCategoryCopy($categoryPageTitle);
    $categoryPageDescription = $category->meta_description
        ?: ($categoryGuide['meta'] ?? ('مشاهده مدل‌های ' . $category->name . '، بررسی قیمت و مقایسه مشخصات فنی در فرجی‌هوم.'));
    $categoryPageDescription = $normalizeCategoryCopy($categoryPageDescription);
    $categoryPageCanonical = $category->canonical_url ?: route('categories.show', $category->slug);
    $categoryOgTitle = $normalizeCategoryCopy($category->og_title ?: $categoryPageTitle);
    $categoryOgDescription = $normalizeCategoryCopy($category->og_description ?: $categoryPageDescription);
    $categoryHeroDescription = $categoryGuide['intro'] ?? (
        filled($category->description)
            ? $normalizeCategoryCopy($category->description)
            : 'مدل‌های این دسته را بررسی کنید و مشخصات، قیمت و گزینه‌های موجود را با نیاز آشپزخانه خود تطبیق دهید.'
    );
    $categoryHeroImage = $category->homepage_image_url ?: $category->image_url;
    $categoryHeroImageSrcset = $category->homepage_image_url
        ? $category->homepage_image_srcset
        : $category->image_srcset;
@endphp

@section('title', $categoryPageTitle)
@section('description', $categoryPageDescription)
@section('canonical', $categoryPageCanonical)
@if(($category->seo_robots ?? 'inherit') !== 'inherit')
    @section('robots', $category->seo_robots)
@endif
@section('og_type', 'website')
@section('og_title', $categoryOgTitle)
@section('og_description', $categoryOgDescription)
@section('og_image', $categoryHeroImage ?? '')



@section('content')

    <div class="bg-[var(--livora-cream)]">

        {{-- =========================================================
             CATEGORY HERO
        ========================================================== --}}
        <section class="farzin-category-detail-banner relative isolate overflow-hidden border-b border-white/10 bg-[var(--livora-ink)] text-white">
            <div aria-hidden="true" class="pointer-events-none absolute -left-24 -top-24 h-80 w-80 rounded-full bg-[var(--livora-accent)]/25 blur-3xl"></div>
            <div aria-hidden="true" class="pointer-events-none absolute bottom-0 right-1/3 h-px w-2/3 bg-gradient-to-l from-transparent via-white/25 to-transparent"></div>

            <x-layout.container>

                <div class="relative py-9 sm:py-14 lg:py-16">

                    {{-- Breadcrumb --}}
                    <nav
                        aria-label="breadcrumb"
                        class="flex flex-wrap items-center gap-2 text-[11px] text-white/55"
                    >

                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-white"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <a
                            href="{{ route('categories.index') }}"
                            class="transition hover:text-[var(--livora-text)]"
                        >
                            دسته‌بندی‌ها
                        </a>

                        <span>/</span>

                        <span class="text-white">
                        {{ $category->name }}
                    </span>

                    </nav>

                    <div class="mt-9 grid items-center gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(280px,400px)] lg:gap-14">

                        <div class="max-w-3xl">

                            <p class="farzin-category-detail__eyebrow text-xs font-bold tracking-wide text-[var(--livora-accent)]">
                                {{ $categoryGuide['eyebrow'] ?? 'محصولات فرجی‌هوم' }}
                            </p>
                            <h1 class="farzin-category-detail__title mt-4 max-w-3xl text-3xl font-black leading-[1.55] tracking-tight text-white sm:text-4xl lg:text-5xl">
                                {{ $categoryGuide['title'] ?? $category->name }}
                            </h1>

                            <p class="farzin-category-detail__description mt-5 max-w-2xl text-sm leading-9 text-white/75 sm:text-base">
                                {{ $categoryHeroDescription }}
                            </p>

                            <div class="mt-7 flex flex-wrap items-center gap-3">

                            <span class="rounded-full border border-white/20 bg-white/5 px-4 py-2 text-[11px] text-white/75">
                                {{ \App\Helpers\PersianNumberHelper::format($products->total()) }}
                                محصول
                            </span>

                                <a
                                    href="{{ route('shop.index', ['category' => $category->slug]) }}"
                                    class="rounded-full bg-white px-5 py-3 text-xs font-bold text-[var(--livora-ink)] transition hover:bg-[var(--livora-accent-soft)]"
                                >
                                    مشاهده همه محصولات
                                </a>

                            </div>

                        </div>

                        {{-- Category image --}}
                        <div class="overflow-hidden rounded-[1.75rem] border border-white/15 bg-white/[0.07] p-2 shadow-2xl shadow-black/20" data-farzin-reveal>

                            @if($categoryHeroImage)

                                <img
                                    src="{{ $categoryHeroImage }}"
                                    @if($categoryHeroImageSrcset) srcset="{{ $categoryHeroImageSrcset }}" sizes="(max-width: 767px) 100vw, 50vw" @endif
                                    alt="{{ $category->name }}"
                                    class="aspect-[4/3] h-full w-full rounded-[1.25rem] object-cover transition duration-700 hover:scale-[1.03]"
                                >

                            @else

                                <div class="flex aspect-[4/3] flex-col items-center justify-center gap-4 rounded-[1.25rem] bg-gradient-to-br from-white/10 to-white/[0.02]">
                                <span class="px-6 text-center text-sm font-bold tracking-wide text-white/80">
                                    {{ $category->name }} / فرجی‌هوم
                                </span>
                                </div>

                            @endif

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        @if($categoryGuide)
            <section class="border-b border-[var(--livora-border)] bg-[var(--livora-surface)]">
                <x-layout.container>
                    <div class="py-12 sm:py-16">
                        <div class="max-w-3xl">
                            <p class="text-xs font-bold text-[var(--livora-accent)]">قبل از انتخاب بررسی کنید</p>
                            <h2 class="mt-2 text-2xl font-black tracking-tight text-[var(--livora-ink)] sm:text-3xl">سه معیار مهم برای انتخاب {{ $category->name }}</h2>
                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">این موارد را در کنار قیمت و موجودی مدل‌ها بررسی کنید تا انتخاب با فضای نصب و نیاز روزمره شما هماهنگ باشد.</p>
                        </div>
                        <div class="mt-8 grid gap-px border border-[var(--livora-border)] bg-[var(--livora-border)] md:grid-cols-3">
                            @foreach($categoryGuide['factors'] as $index => $factor)
                                <article class="bg-white p-6 sm:p-7">
                                    <span class="text-xs font-bold text-[var(--livora-accent)]">۰{{ $index + ۱ }}</span>
                                    <h3 class="mt-4 text-lg font-black text-[var(--livora-ink)]">{{ $factor['title'] }}</h3>
                                    <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">{{ $factor['body'] }}</p>
                                </article>
                            @endforeach
                        </div>
                        <div class="mt-6 flex flex-col gap-4 border-r-2 border-[var(--livora-accent)] bg-white px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                            <p class="text-xs leading-7 text-[var(--livora-stone)]">{{ $categoryGuide['note'] }}</p>
                            <a href="{{ route('contact') }}" class="inline-flex min-h-11 w-fit shrink-0 items-center gap-2 text-sm font-bold text-[var(--livora-accent)] hover:underline">
                                پرسش پیش از خرید <span aria-hidden="true">←</span>
                            </a>
                        </div>
                    </div>
                </x-layout.container>
            </section>
        @endif

        @include('categories.partials.attribute-filters')

        {{-- =========================================================
             محصولات
        ========================================================== --}}
        <section>

            <x-layout.container>

                <div class="py-10 sm:py-14">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                محصولات
                            </p>

                            <h2 class="mt-2 text-2xl font-black tracking-tight text-[var(--livora-ink)] sm:text-3xl">
                                مدل‌های موجود {{ $category->name }}
                            </h2>

                        </div>

                        <a
                            href="{{ route('shop.index') }}"
                            class="text-sm font-medium text-[var(--livora-accent)]"
                        >
                            بازگشت به فروشگاه
                            <span class="mr-1">←</span>
                        </a>

                    </div>


                    @if($products->isNotEmpty())

                        <div class="farzin-category-product-grid mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-2 xl:grid-cols-4">

                            @foreach($products as $product)

                                <x-product.card
                                    :product="$product"
                                />

                            @endforeach

                        </div>

                        <div class="mt-14">
                            {{ $products->withQueryString()->links() }}
                        </div>

                    @else

                        <div class="mt-8 rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] px-6 py-24 text-center">

                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--livora-surface)] text-sm text-[var(--livora-stone)]">
                                —
                            </div>

                            <h2 class="mt-6 text-2xl font-semibold">
                                محصولی در این دسته پیدا نشد
                            </h2>

                            <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-[var(--livora-stone)]">
                                در حال حاضر محصولی برای نمایش در این دسته ثبت نشده است. می‌توانید دسته‌های دیگر را ببینید یا برای اطلاع از مدل‌های موجود با فرجی‌هوم تماس بگیرید.
                            </p>

                            <a
                                href="{{ route('categories.index') }}"
                                class="mt-7 inline-flex rounded-2xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >
                                مشاهده دسته‌بندی‌ها
                            </a>

                        </div>

                    @endif

                </div>

            </x-layout.container>

        </section>


        @include('categories.partials.comparison')

        @if(filled($category->content_title) || filled($category->content_body) || filled($category->content_image_url))
            <section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">
                <x-layout.container>
                    <div class="grid items-center gap-8 py-12 sm:py-16 lg:grid-cols-2 lg:gap-12">
                        <div class="min-w-0">
                            <p class="text-[10px] font-medium tracking-[0.18em] text-[var(--livora-accent)]">راهنمای دسته‌بندی</p>
                            <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">
                                {{ $category->content_title ?: 'راهنمای انتخاب ' . $category->name }}
                            </h2>
                            @if(filled($category->content_body))
                                <div class="mt-5 whitespace-pre-line text-sm leading-8 text-[var(--livora-stone)]">{{ $category->content_body }}</div>
                            @endif
                        </div>
                        @if($category->content_image_url)
                            <figure class="overflow-hidden rounded-xl bg-[var(--livora-surface)]">
                                <img
                                    src="{{ $category->content_image_url }}"
                                    @if($category->content_image_srcset) srcset="{{ $category->content_image_srcset }}" sizes="(max-width: 1023px) 100vw, 50vw" @endif
                                    alt="{{ $category->content_title ?: 'راهنمای ' . $category->name }}"
                                    loading="lazy"
                                    class="aspect-[16/9] w-full object-cover"
                                >
                            </figure>
                        @endif
                    </div>
                </x-layout.container>
            </section>
        @endif


        @include('categories.partials.faqs')

        {{-- =========================================================
             FINAL CTA
        ========================================================== --}}
        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="flex flex-col gap-6 py-12 sm:flex-row sm:items-center sm:justify-between sm:py-16">

                    <div>

                        <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            فرجی‌هوم
                        </p>

                        <h2 class="mt-2 text-2xl font-semibold">
                            هنوز انتخابت را پیدا نکردی؟
                        </h2>

                        <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">
                            مجموعه کامل فروشگاه را بررسی کن.
                        </p>

                    </div>

                    <a
                        href="{{ route('shop.index') }}"
                        class="inline-flex w-fit rounded-2xl bg-[var(--livora-ink)] px-6 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                    >
                        مشاهده فروشگاه
                    </a>

                </div>

            </x-layout.container>

        </section>

    </div>

@endsection
