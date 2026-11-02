@extends('layouts.app')

@php
    $categoryPageTitle = $category->meta_title ?: ($category->name . ' | خرید هود و سینک آشپزخانه | فرزین');
    $categoryPageDescription = $category->meta_description
        ?: ('مشاهده و خرید محصولات دسته‌بندی ' . $category->name . ' در فرزین؛ بررسی قیمت و مشخصات فنی محصولات.');
    $categoryPageCanonical = $category->canonical_url ?: route('categories.show', $category->slug);
    $categoryOgTitle = $category->og_title ?: $categoryPageTitle;
    $categoryOgDescription = $category->og_description ?: $categoryPageDescription;

    $categoryGuide = match ($category->slug) {
        'hoods' => [
            'heading' => 'پیش از انتخاب هود چه چیزهایی را بررسی کنیم؟',
            'paragraphs' => [
                'انتخاب هود را با اندازه اجاق و فضای نصب شروع کنید. عرض دستگاه، عمق کابینت، فاصله تا سطح پخت و فضای لازم برای نصب باید با نقشه و راهنمای همان مدل تطبیق داده شوند. هود مخفی، مورب، شومینه‌ای و جزیره‌ای از نظر فرم نصب و فضای موردنیاز یکسان نیستند؛ بنابراین تنها به ظاهر تصویر اکتفا نکنید و محل قرارگیری موتور، خروجی هوا و مسیر کانال را هم بررسی کنید.',
                'قدرت مکش، سطح صدا، نوع فیلتر، تعداد دور موتور، روشنایی و روش نظافت از مشخصاتی هستند که باید در صفحه هر مدل و برگه فنی سازنده بررسی شوند. عدد و ویژگی یک مدل را به مدل دیگر تعمیم ندهید. اگر مسیر خروجی یا اندازه کابینت مشخص نیست، پیش از سفارش عکس و ابعاد محل نصب را برای بررسی با مجموعه در میان بگذارید.',
                'برای مقایسه، ابتدا نوع نصب و عرض موردنیاز را مشخص کنید، سپس مدل‌های موجود را از نظر ابعاد، امکانات و قیمت کنار هم قرار دهید. موجودی هر گزینه و شرایط واقعی گارانتی، ارسال و نصب را در صفحه محصول یا از راه‌های تماس مجموعه بررسی کنید؛ این شرایط ممکن است بین مدل‌ها متفاوت باشد.',
            ],
            'faqs' => [
                ['q' => 'از کجا نوع مناسب هود را انتخاب کنم؟', 'a' => 'نوع نصب کابینت، محل اجاق و مسیر خروجی هوا را مشخص کنید و سپس مدل‌های هم‌نوع را با ابعاد و نقشه نصب مقایسه کنید.'],
                ['q' => 'آیا قدرت مکش همه مدل‌ها یکسان است؟', 'a' => 'خیر؛ مقدار مکش و سطح صدا باید از مشخصات فنی تأییدشده همان مدل خوانده شود.'],
                ['q' => 'برای بررسی نصب چه اطلاعاتی لازم است؟', 'a' => 'عرض فضای نصب، نوع کابینت، محل خروجی هوا و چند عکس واضح از محل نصب به بررسی دقیق‌تر کمک می‌کند.'],
            ],
        ],
        'sinks' => [
            'heading' => 'راهنمای انتخاب سینک متناسب با کابینت',
            'paragraphs' => [
                'پیش از انتخاب سینک، عرض کابینت، فضای برش صفحه، محل لوله‌کشی و جای شیر آب را اندازه بگیرید. ابعاد بیرونی سینک با اندازه برش موردنیاز یکسان نیست؛ نقشه نصب و اندازه برش هر مدل را جداگانه بررسی کنید. همچنین مشخص کنید نصب روکار، توکار یا زیرکار برای جنس صفحه و شیوه اجرای آشپزخانه شما مناسب است.',
                'تعداد و عمق لگن، جهت سینی، محل سوراخ شیر، ضخامت و جنس بدنه، نوع پوشش و اقلام همراه از ویژگی‌های مهم در مقایسه هستند. در مدل‌های استیل و گرانیتی، روش نظافت و مواد مجاز برای نگهداری را مطابق راهنمای تولیدکننده بررسی کنید. از روی نام یا تصویر محصول، جنس دقیق، ضخامت یا کیفیت فنی را حدس نزنید؛ این موارد باید در مشخصات همان مدل درج شده باشند.',
                'برای انتخاب نهایی، مدل‌هایی را مقایسه کنید که با ابعاد کابینت و روش نصب شما سازگارند. اگر بین دو اندازه یا نوع نصب مردد هستید، پیش از سفارش تصویر صفحه کابینت، اندازه داخلی کابینت و محل لوله‌ها را آماده کنید و از مجموعه راهنمایی بخواهید. قیمت، موجودی، اقلام همراه و شرایط خدمات را در صفحه همان محصول بررسی کنید.',
            ],
            'faqs' => [
                ['q' => 'اندازه برش صفحه را از کجا پیدا کنم؟', 'a' => 'نقشه نصب یا مشخصات فنی همان مدل مرجع اندازه برش است؛ اندازه بیرونی سینک را جایگزین آن نکنید.'],
                ['q' => 'روکار، توکار و زیرکار چه تفاوتی دارند؟', 'a' => 'تفاوت اصلی در شیوه قرارگیری لبه سینک نسبت به صفحه و آماده‌سازی کابینت است؛ سازگاری آن را با جنس صفحه و نقشه نصب بررسی کنید.'],
                ['q' => 'برای انتخاب جنس سینک به چه نکته‌ای توجه کنم؟', 'a' => 'نوع استفاده، روش نظافت، مقاومت موردنیاز و مشخصات اعلام‌شده برای همان مدل را با هم مقایسه کنید.'],
            ],
        ],
        default => [
            'heading' => 'چطور محصولات این دسته را مقایسه کنیم؟',
            'paragraphs' => [
                'برای انتخاب دقیق، ابتدا فضای نصب و نیاز خود را مشخص کنید. ابعاد، نوع نصب، جنس، امکانات و اقلام همراه را از مشخصات اختصاصی هر محصول بخوانید و مدل‌ها را بر پایه ویژگی‌های قابل مقایسه کنار هم قرار دهید.',
                'تصویر محصول برای شناخت ظاهر و فرم آن مفید است، اما جای نقشه نصب و مشخصات فنی را نمی‌گیرد. هرجا اندازه برش، نوع اتصال یا شرایط نگهداری اهمیت دارد، مستند همان مدل را بررسی کنید و از تعمیم مشخصات یک محصول به محصول دیگر خودداری کنید.',
                'قیمت و موجودی را در صفحه محصول کنترل کنید. شرایط گارانتی، ارسال و نصب تنها زمانی در سایت اعلام می‌شوند که در تنظیمات مجموعه ثبت شده باشند؛ برای جزئیات موردی می‌توانید از صفحه تماس با فرزین سؤال کنید.',
            ],
            'faqs' => [
                ['q' => 'چطور محصول مناسب فضای خود را پیدا کنم؟', 'a' => 'ابعاد محل نصب و ویژگی‌های موردنیاز را مشخص کنید و سپس مدل‌ها را بر اساس مشخصات ثبت‌شده مقایسه کنید.'],
                ['q' => 'آیا قیمت و موجودی ثابت است؟', 'a' => 'قیمت و وضعیت موجودی فعلی را در صفحه همان محصول بررسی کنید.'],
                ['q' => 'برای پرسیدن درباره نصب یا خدمات چه کنم؟', 'a' => 'از طریق صفحه تماس، مدل و شرایط محل نصب را برای بررسی مطرح کنید.'],
            ],
        ],
    };
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
@section('og_image', $category->image_url ?? '')



@section('content')

    <div class="bg-[var(--livora-cream)]">

        {{-- =========================================================
             CATEGORY HERO
        ========================================================== --}}
        <section class="farzin-category-detail-banner border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-8 sm:py-12">

                    {{-- Breadcrumb --}}
                    <nav
                        aria-label="breadcrumb"
                        class="flex flex-wrap items-center gap-2 text-[11px] text-[var(--livora-stone)]"
                    >

                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--livora-text)]"
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

                        <span class="text-[var(--livora-text)]">
                        {{ $category->name }}
                    </span>

                    </nav>

                    <div class="mt-8 grid items-end gap-8 lg:grid-cols-[minmax(0,1fr)_360px]">

                        <div class="max-w-3xl">

                            <p class="farzin-category-detail__eyebrow text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                راهنمای دسته‌بندی
                            </p>

                            <h1 class="farzin-category-detail__title mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">
                                {{ $category->name }}
                            </h1>

                            <p class="mt-5 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">
                                {{ $category->description ?: 'مدل‌های موجود این دسته را بر اساس ابعاد، نوع نصب و مشخصات فنی مقایسه کنید.' }}
                            </p>

                            <div class="mt-7 flex flex-wrap items-center gap-3">

                            <span class="rounded-full border border-[var(--livora-border)] px-4 py-2 text-[11px] text-[var(--livora-stone)]">
                                {{ \App\Helpers\PersianNumberHelper::format($products->total()) }}
                                محصول
                            </span>

                                <a
                                    href="{{ route('shop.index', ['category' => $category->slug]) }}"
                                    class="rounded-full bg-[var(--livora-ink)] px-4 py-2 text-[11px] font-medium text-white transition hover:bg-[var(--livora-accent)]"
                                >
                                    مشاهده همه محصولات
                                </a>

                            </div>

                        </div>

                        {{-- Category image --}}
                        <div class="overflow-hidden rounded-[2rem] bg-[var(--livora-surface)]" data-farzin-reveal>

                            @if($category->homepage_image_url)

                                <img
                                    src="{{ $category->homepage_image_url }}"
                                    @if($category->homepage_image_srcset) srcset="{{ $category->homepage_image_srcset }}" sizes="(max-width: 767px) 100vw, 50vw" @endif
                                    alt="{{ $category->name }}"
                                    class="aspect-[4/3] h-full w-full object-cover transition duration-700 hover:scale-[1.03]"
                                >

                            @else

                                <div class="flex aspect-[4/3] items-center justify-center">
                                <span class="text-xs tracking-[0.2em] text-[var(--livora-stone)]">
                                    فرزین
                                </span>
                                </div>

                            @endif

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             محصولات موجود
        ========================================================== --}}
        <section>

            <x-layout.container>

                <div class="py-10 sm:py-14">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                PRODUCTS
                            </p>

                            <h2 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">
                                محصولات این مجموعه
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

                        <div class="mt-8 grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-2 xl:grid-cols-4">

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
                                در حال حاضر محصولی در این مجموعه قرار نگرفته است.
                                می‌توانید سایر دسته‌بندی‌ها را بررسی کنید.
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


        {{-- =========================================================
             CATEGORY SEO CONTENT
        ========================================================== --}}
        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="max-w-4xl py-14 sm:py-18">

                    <p class="text-[10px] font-medium text-[var(--livora-accent)]">راهنمای انتخاب</p>
                    <h2 class="mt-4 text-2xl font-semibold tracking-tight sm:text-3xl">{{ $categoryGuide['heading'] }}</h2>

                    <div class="mt-5 space-y-4 text-sm leading-8 text-[var(--livora-stone)]">
                        @foreach($categoryGuide['paragraphs'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    </div>

                    <div class="mt-10 border-t border-[var(--livora-border)] pt-8">
                        <h3 class="text-xl font-semibold">پرسش‌های رایج درباره {{ $category->name }}</h3>
                        <div class="mt-5 space-y-3">
                            @foreach($categoryGuide['faqs'] as $faq)
                                <details class="rounded-xl border border-[var(--livora-border)] bg-[var(--livora-cream)] p-4">
                                    <summary class="cursor-pointer text-sm font-semibold">{{ $faq['q'] }}</summary>
                                    <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">{{ $faq['a'] }}</p>
                                </details>
                            @endforeach
                        </div>
                    </div>

                    <a href="{{ route('contact') }}" class="mt-8 inline-flex min-h-11 items-center rounded-xl bg-[var(--livora-ink)] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[var(--livora-accent)]">
                        برای انتخاب راهنمایی بگیرید
                    </a>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FINAL CTA
        ========================================================== --}}
        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="flex flex-col gap-6 py-12 sm:flex-row sm:items-center sm:justify-between sm:py-16">

                    <div>

                        <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            فرزین
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
