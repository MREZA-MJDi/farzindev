@extends('layouts.app')

@section('title', 'درباره فرزین فرجی | تولید هود و سینک آشپزخانه')

@section(
    'description',
    'با فرزین فرجی و مجموعه فعال در حوزه تولید هود و سینک آشپزخانه آشنا شوید؛ معرفی مجموعه، رویکرد تولید و کاتالوگ محصولات.'
)

@section('canonical', route('about'))

@push('seo')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "AboutPage",
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

    <div class="overflow-hidden bg-[var(--livora-cream)]">

        {{-- =========================================================
             HERO
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-10 sm:py-16 lg:py-20">

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

                        <span class="text-[var(--livora-text)]">
                        درباره ما
                    </span>

                    </nav>

                    <div class="mt-10 grid items-end gap-10 lg:grid-cols-[1fr_0.8fr]">

                        <div class="max-w-4xl">

                            <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">
                                ABOUT FARZIN
                            </p>

                            <h1 class="mt-5 text-5xl font-semibold leading-[1.02] tracking-tight sm:text-6xl lg:text-7xl">
                                فرزین فرجی؛
                                <span class="block text-[var(--livora-accent)]">
                                    از تولید تا جزئیات آشپزخانه.
                                </span>
                            </h1>

                        </div>

                        <div>

                            <p class="text-sm leading-8 text-[var(--livora-stone)] sm:text-base">
                                فرزین فرجی در حوزه تولید هود و سینک آشپزخانه فعالیت می‌کند.
                                این وب‌سایت برای معرفی مجموعه، نمایش محصولات و کمک به شناخت دقیق‌تر مدل‌ها و مشخصات آن‌ها طراحی شده است.
                                جزئیات هر محصول را می‌توانید در کاتالوگ فرزین بررسی کنید.
                            </p>

                            <a
                                href="{{ route('shop.index') }}"
                                class="mt-7 inline-flex items-center rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium !text-white transition-colors duration-300 hover:bg-[var(--livora-accent)] hover:!text-white"
                            >
                                مشاهده محصولات فرزین
                            </a>
                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             BRAND STATEMENT
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)]">

            <x-layout.container>

                <div class="grid gap-10 py-16 sm:py-20 lg:grid-cols-[0.7fr_1.3fr] lg:py-24">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            THE MAKER AND THE WORK
                        </p>

                        <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                            تولید، محصول و تجربه‌ی استفاده.
                        </h2>

                    </div>

                    <div class="max-w-3xl">

                        <p class="text-lg leading-9 text-[var(--livora-text)] sm:text-xl">
                            محصول برای ما فقط یک تصویر یا نام در کاتالوگ نیست؛
                            ابعاد، کاربرد و مشخصات آن باید برای خریدار قابل بررسی باشد.
                            معرفی روشن محصول کمک می‌کند انتخاب بر پایه‌ی نیاز واقعی آشپزخانه انجام شود.
                        </p>

                        <p class="mt-6 text-sm leading-8 text-[var(--livora-stone)]">
                            در کنار معرفی مجموعه و مسیر کاری آن، کاتالوگ محصولات نیز بخشی از همین تجربه است:
                            اطلاعات هر مدل باید کمک کند خریدار پیش از سفارش، تفاوت‌ها و تناسب آن را با فضای خود بررسی کند.
                        </p>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FACTORY AND WORK
        ========================================================== --}}
        <section class="relative overflow-hidden bg-[var(--livora-ink)] text-white">
            <div aria-hidden="true" class="pointer-events-none absolute -right-24 top-0 h-72 w-72 rounded-full bg-[var(--livora-accent)]/20 blur-3xl"></div>
            <x-layout.container>
                <div class="relative grid gap-10 py-16 sm:py-20 lg:grid-cols-[0.85fr_1.15fr] lg:items-end lg:py-24">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-[#b7c9cb]">FACTORY & PROCESS</p>
                        <h2 class="mt-5 text-3xl font-semibold leading-tight sm:text-4xl lg:text-5xl">
                            پشت محصول را هم بشناسید.
                        </h2>
                    </div>
                    <div class="max-w-3xl">
                        <p class="text-base leading-9 text-white/80 sm:text-lg">
                            برای شناخت یک هود یا سینک، ظاهر محصول تنها بخشی از ماجراست.
                            ابعاد، نوع نصب، جنس، امکانات و شیوه نگهداری هم اهمیت دارند.
                            معرفی فرزین قرار است مسیر آشنایی با مجموعه و محصولات را روشن‌تر کند.
                        </p>
                        <p class="mt-5 text-sm leading-8 text-white/60">
                            اطلاعات اختصاصی هر مدل باید با مشخصات فنی تأییدشده همان محصول همراه باشد؛
                            به همین دلیل ویژگی‌ها و جزئیات قابل‌بررسی در صفحه محصول ارائه می‌شوند.
                        </p>
                        <a href="{{ route('shop.index') }}" class="mt-7 inline-flex items-center gap-3 rounded-xl bg-[var(--livora-accent)] px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-[var(--livora-white)] hover:text-[var(--livora-text)]">
                            دیدن کاتالوگ محصولات <span aria-hidden="true">←</span>
                        </a>
                    </div>
                </div>
            </x-layout.container>
        </section>

        {{-- =========================================================
             VALUES
        ========================================================== --}}
        <section class="bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="py-16 sm:py-20 lg:py-24">

                    <div class="max-w-2xl">

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
OUR FOCUS
                        </p>

                        <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                            تمرکز مجموعه فرزین.
                        </h2>

                    </div>

                    <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-4">

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            01
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                کاربرد
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                فرم و ظاهر باید در خدمت فضای شما باشد، نه اینکه فضا را تحت‌الشعاع قرار دهد.
                            </p>

                        </article>

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            02
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                مشخصات روشن
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                انتخاب متریال و ساخت خوب، چیزی است که ارزش یک محصول را در طول زمان حفظ می‌کند.
                            </p>

                        </article>

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            03
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                انتخاب مناسب
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                قیمت، مشخصات، موجودی و شرایط پرداخت باید قبل از تصمیم‌گیری روشن باشند.
                            </p>

                        </article>

                        <article class="rounded-[2rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-7">

                        <span class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            04
                        </span>

                            <h3 class="mt-8 text-lg font-semibold">
                                مسیر تولید
                            </h3>

                            <p class="mt-3 text-sm leading-8 text-[var(--livora-stone)]">
                                خرید باید ساده و آرام باشد؛ از اولین مشاهده محصول تا سفارش نهایی.
                            </p>

                        </article>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             PROCESS
        ========================================================== --}}
        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="grid gap-10 py-16 sm:py-20 lg:grid-cols-[0.65fr_1.35fr] lg:py-24">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            THE EXPERIENCE
                        </p>

                        <h2 class="mt-4 text-3xl font-semibold tracking-tight sm:text-4xl">
                            از کشف تا انتخاب.
                        </h2>

                        <p class="mt-4 text-sm leading-8 text-[var(--livora-stone)]">
                            تجربه فرزین فقط به صفحه محصول محدود نمی‌شود.
                            تمام مسیر برای انتخاب راحت‌تر طراحی شده است.
                        </p>

                    </div>

                    <div class="space-y-3">

                        <article class="rounded-3xl border border-[var(--livora-border)] p-5 sm:p-6">

                            <div class="flex gap-5">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                01
                            </span>

                                <div>

                                    <h3 class="text-sm font-semibold">
                                        کشف
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        دسته‌بندی‌ها و مجموعه‌های مختلف را بررسی می‌کنید و محصول مناسب فضای خود را پیدا می‌کنید.
                                    </p>

                                </div>

                            </div>

                        </article>

                        <article class="rounded-3xl border border-[var(--livora-border)] p-5 sm:p-6">

                            <div class="flex gap-5">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                02
                            </span>

                                <div>

                                    <h3 class="text-sm font-semibold">
                                        بررسی
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        قیمت، مشخصات، موجودی و در صورت وجود، شرایط خرید اقساطی محصول را می‌بینید.
                                    </p>

                                </div>

                            </div>

                        </article>

                        <article class="rounded-3xl border border-[var(--livora-border)] p-5 sm:p-6">

                            <div class="flex gap-5">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                03
                            </span>

                                <div>

                                    <h3 class="text-sm font-semibold">
                                        انتخاب
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        محصول و ویژگی‌های موردنظر را انتخاب و وارد سبد خرید می‌کنید.
                                    </p>

                                </div>

                            </div>

                        </article>

                        <article class="rounded-3xl border border-[var(--livora-border)] p-5 sm:p-6">

                            <div class="flex gap-5">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-surface)] text-xs font-semibold">
                                04
                            </span>

                                <div>

                                    <h3 class="text-sm font-semibold">
                                        پرداخت
                                    </h3>

                                    <p class="mt-2 text-xs leading-7 text-[var(--livora-stone)]">
                                        مسیر پرداخت متناسب با سفارش انتخاب می‌شود و شرایط اقساطی در صورت فعال بودن، شفاف نمایش داده خواهد شد.
                                    </p>

                                </div>

                            </div>

                        </article>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             INSTALLMENT
        ========================================================== --}}
        <section class="border-t border-white/10 bg-[var(--livora-ink)] text-white">

            <x-layout.container>

                <div class="grid gap-10 py-16 sm:py-20 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:py-24">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-white/45">
                            FLEXIBLE PAYMENT
                        </p>

                        <h2 class="mt-4 max-w-2xl text-3xl font-semibold tracking-tight sm:text-4xl">
                            انتخاب خوب نباید فقط به بودجه امروز محدود شود.
                        </h2>

                        <p class="mt-5 max-w-2xl text-sm leading-8 text-white/55">
                            برخی محصولات فرزین می‌توانند با شرایط اقساطی تعریف‌شده توسط فروشگاه خریداری شوند.
                            درصد پیش‌پرداخت، تعداد چک و فاصله سررسید برای هر محصول مشخص است.
                        </p>

                        <a
                            href="{{ route('shop.index') }}"
                            class="mt-8 inline-flex items-center rounded-2xl bg-[var(--livora-white)] px-6 py-4 text-sm font-medium text-[var(--livora-text)] transition hover:bg-[var(--livora-cream)]"
                        >
                            مشاهده محصولات
                        </a>

                    </div>

                    <div class="grid grid-cols-2 gap-3">

                        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 sm:p-6">

                            <p class="text-[10px] uppercase tracking-[0.18em] text-white/40">
                                TODAY
                            </p>

                            <p class="mt-4 text-3xl font-semibold">
                                50٪
                            </p>

                            <p class="mt-2 text-xs leading-6 text-white/40">
                                نمونه پیش‌پرداخت
                            </p>

                        </div>

                        <div class="rounded-3xl border border-white/10 bg-white/5 p-5 sm:p-6">

                            <p class="text-[10px] uppercase tracking-[0.18em] text-white/40">
                                SCHEDULE
                            </p>

                            <p class="mt-4 text-3xl font-semibold">
                                2+
                            </p>

                            <p class="mt-2 text-xs leading-6 text-white/40">
                                فقره چک قابل تنظیم
                            </p>

                        </div>

                        <div class="col-span-2 rounded-3xl border border-white/10 bg-white/[0.07] p-5 sm:p-6">

                            <p class="text-sm font-semibold">
                                شرایط هر محصول در صفحه خودش نمایش داده می‌شود.
                            </p>

                            <p class="mt-2 text-xs leading-7 text-white/45">
                                قبل از افزودن محصول به سبد، مبلغ پیش‌پرداخت و برنامه تسویه را بررسی کنید.
                            </p>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             FINAL STATEMENT
        ========================================================== --}}
        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="py-20 sm:py-28">

                    <div class="mx-auto max-w-3xl text-center">

                        <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">
                            فرزین
                        </p>

                        <h2 class="mt-5 text-4xl font-semibold tracking-tight sm:text-5xl">
                            برای فضاهایی که قرار است ماندگار باشند.
                        </h2>

                        <p class="mx-auto mt-5 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">
                            مجموعه فرزین را ببین و محصولی را پیدا کن که فقط خانه را پر نکند؛
                            بلکه بخشی از شخصیت آن شود.
                        </p>

                        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">

                            <a
                                href="{{ route('shop.index') }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-[var(--livora-ink)] px-7 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >
                                ورود به فروشگاه
                            </a>

                            <a
                                href="{{ route('contact') }}"
                                class="inline-flex items-center justify-center rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-7 py-4 text-sm font-medium text-[var(--livora-text)] transition hover:border-[var(--livora-ink)]"
                            >
                                تماس با ما
                            </a>

                        </div>

                    </div>

                </div>

            </x-layout.container>

        </section>

    </div>

@endsection
