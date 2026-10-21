@extends('layouts.app')

@section('title', 'بلاگ فرزین | راهنمای انتخاب هود و سینک')
@section('description', 'راهنمای خرید هود آشپزخانه و سینک ظرفشویی از فرزین؛ نکته‌هایی درباره ابعاد، نوع نصب، جنس و نگهداری.')
@section('canonical', route('blog.index'))

@push('seo')
<script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Blog",
        "name": "بلاگ فرزین",
        "url": @json(route('blog.index')),
        "inLanguage": "fa-IR"
    }
    </script>
@endpush

@section('content')
<div class="overflow-hidden bg-[var(--livora-cream)]">
    <section class="relative overflow-hidden bg-[var(--livora-ink)] text-white">
        <div class="pointer-events-none absolute -left-24 -top-28 h-80 w-80 rounded-full border border-white/10"></div>
        <div class="pointer-events-none absolute -left-10 -top-14 h-52 w-52 rounded-full border border-white/10"></div>
        <x-layout.container>
            <div class="relative py-14 sm:py-20 lg:py-24">
                <nav aria-label="مسیر صفحه" class="flex items-center gap-2 text-xs text-white/55">
                    <a href="{{ route('home') }}" class="transition hover:text-white">خانه</a>
                    <span>/</span>
                    <span class="text-[#b7c9cb]">بلاگ فرزین</span>
                </nav>
                <div class="mt-10 grid gap-10 lg:grid-cols-[1fr_0.55fr] lg:items-end">
                    <div class="max-w-4xl">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.28em] text-[#b7c9cb]">FARZIN / FIELD NOTES</p>
                        <h1 class="mt-5 text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-7xl">
                            انتخاب آگاهانه،
                            <span class="block text-[#b7c9cb]">از همین‌جا شروع می‌شود.</span>
                        </h1>
                    </div>
                    <p class="max-w-xl text-sm leading-8 text-white/65 sm:text-base">
                        نکته‌های کاربردی برای انتخاب هود آشپزخانه و سینک ظرفشویی؛
                        از اندازه‌گیری و نوع نصب تا شناخت ویژگی‌هایی که واقعاً در استفاده روزانه اهمیت دارند.
                    </p>
                </div>
                <div class="mt-12 flex flex-wrap gap-3">
                    <a href="#articles" class="inline-flex items-center rounded-2xl bg-[var(--livora-white)] px-5 py-3.5 text-sm font-semibold text-[var(--livora-text)] transition hover:bg-[#d6dfe0]">خواندن راهنماها <span class="mr-2">↓</span></a>
                    <a href="{{ route('categories.index') }}" class="inline-flex items-center rounded-2xl border border-white/20 px-5 py-3.5 text-sm font-medium text-white transition hover:border-white/60 hover:text-white">مشاهده محصولات</a>
                </div>
            </div>
        </x-layout.container>
    </section>

    <section id="articles" class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">
        <x-layout.container>
            <div class="flex flex-col gap-4 py-12 sm:flex-row sm:items-end sm:justify-between sm:py-16">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-[var(--livora-accent)]">THE BUYING GUIDE</p>
                    <h2 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">راهنماهای انتخاب فرزین</h2>
                </div>
                <p class="max-w-lg text-sm leading-7 text-[var(--livora-stone)]">قبل از تصمیم نهایی، چند نکته مهم را بررسی کنید و مشخصات هر مدل را با فضای آشپزخانه خود تطبیق دهید.</p>
            </div>

            <div class="grid gap-5 pb-14 sm:grid-cols-2 lg:grid-cols-3 lg:pb-20">
                <a href="#hood-guide" class="group flex flex-col rounded-[1.75rem] border border-[var(--livora-border)] bg-[var(--livora-cream)] p-6 transition duration-300 hover:-translate-y-1 hover:border-[var(--livora-accent)] hover:shadow-[var(--livora-shadow-md)] sm:p-7">
                    <div class="flex items-center justify-between">
                        <span class="rounded-full bg-[var(--livora-surface)] px-3 py-1.5 text-[10px] font-semibold text-[var(--livora-text)]">هود آشپزخانه</span>
                        <span class="text-xs text-[var(--livora-stone)]">راهنمای ۰۱</span>
                    </div>
                    <div class="mt-8 flex h-28 items-end overflow-hidden rounded-2xl bg-gradient-to-br from-[#111416] via-[#354247] to-[#8b9b9f] p-5">
                        <span class="text-2xl font-semibold text-white">انتخاب هود</span>
                    </div>
                    <h3 class="mt-6 text-xl font-semibold transition group-hover:text-[var(--livora-accent)]">قبل از خرید هود به چه نکاتی توجه کنیم؟</h3>
                    <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">ابعاد، نوع نصب، مسیر خروج هوا، میزان صدا و دسترسی به فیلترها را کنار هم بررسی کنید.</p>
                    <span class="mt-6 text-sm font-semibold text-[var(--livora-accent)]">مطالعه راهنما <span class="mr-1">←</span></span>
                </a>

                <a href="#sink-guide" class="group flex flex-col rounded-[1.75rem] border border-[var(--livora-border)] bg-[var(--livora-cream)] p-6 transition duration-300 hover:-translate-y-1 hover:border-[var(--livora-accent)] hover:shadow-[var(--livora-shadow-md)] sm:p-7">
                    <div class="flex items-center justify-between">
                        <span class="rounded-full bg-[var(--livora-surface)] px-3 py-1.5 text-[10px] font-semibold text-[var(--livora-text)]">سینک ظرفشویی</span>
                        <span class="text-xs text-[var(--livora-stone)]">راهنمای ۰۲</span>
                    </div>
                    <div class="mt-8 flex h-28 items-end overflow-hidden rounded-2xl bg-gradient-to-br from-[#202629] via-[#59666a] to-[#a9bdc0] p-5">
                        <span class="text-2xl font-semibold text-white">شناخت سینک</span>
                    </div>
                    <h3 class="mt-6 text-xl font-semibold transition group-hover:text-[var(--livora-accent)]">چطور سینک متناسب با آشپزخانه انتخاب کنیم؟</h3>
                    <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">جنس، تعداد لگن، عمق، ابعاد برش کابینت و محل شیرآلات در انتخاب مناسب اثر دارند.</p>
                    <span class="mt-6 text-sm font-semibold text-[var(--livora-accent)]">مطالعه راهنما <span class="mr-1">←</span></span>
                </a>

                <a href="#measure-guide" class="group flex flex-col rounded-[1.75rem] border border-[var(--livora-border)] bg-[var(--livora-cream)] p-6 transition duration-300 hover:-translate-y-1 hover:border-[var(--livora-accent)] hover:shadow-[var(--livora-shadow-md)] sm:p-7">
                    <div class="flex items-center justify-between">
                        <span class="rounded-full bg-[var(--livora-surface)] px-3 py-1.5 text-[10px] font-semibold text-[var(--livora-text)]">قبل از سفارش</span>
                        <span class="text-xs text-[var(--livora-stone)]">راهنمای ۰۳</span>
                    </div>
                    <div class="mt-8 flex h-28 items-end overflow-hidden rounded-2xl bg-gradient-to-br from-[#111416] via-[#38474b] to-[#a9bdc0] p-5">
                        <span class="text-2xl font-semibold text-white">اندازه‌گیری دقیق</span>
                    </div>
                    <h3 class="mt-6 text-xl font-semibold transition group-hover:text-[var(--livora-accent)]">چک‌لیست اندازه‌گیری پیش از خرید</h3>
                    <p class="mt-3 text-sm leading-7 text-[var(--livora-stone)]">چند اندازه و جزئیات نصب را پیش از نهایی کردن سفارش یادداشت کنید تا انتخاب دقیق‌تری داشته باشید.</p>
                    <span class="mt-6 text-sm font-semibold text-[var(--livora-accent)]">مطالعه راهنما <span class="mr-1">←</span></span>
                </a>
            </div>
        </x-layout.container>
    </section>

    <section class="py-14 sm:py-20">
        <x-layout.container>
            <div class="mx-auto max-w-4xl space-y-6">
                <article id="hood-guide" class="scroll-mt-28 rounded-[1.75rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-9">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--livora-accent)]">GUIDE 01 / HOODS</p>
                    <h2 class="mt-3 text-2xl font-semibold sm:text-3xl">قبل از خرید هود آشپزخانه چه چیزهایی را بررسی کنیم؟</h2>
                    <div class="mt-5 space-y-4 text-sm leading-8 text-[var(--livora-stone)]">
                        <p>اول عرض اجاق و فضای قابل نصب را اندازه بگیرید و آن را با ابعاد درج‌شده در مشخصات همان مدل تطبیق دهید. محل نصب هود و فاصله مجاز از سطح پخت باید طبق راهنمای سازنده باشد.</p>
                        <p>سپس نوع نصب و مسیر خروج هوا را بررسی کنید. بسته به مدل، امکان نصب، نیاز به کانال و شرایط کابینت متفاوت است. اگر آشپزخانه آماده است یا مسیر خروج هوا محدودیت دارد، قبل از سفارش با نصاب مشورت کنید.</p>
                        <p>میزان صدا، نوع فیلتر، روش نظافت، امکانات کنترلی، گارانتی و خدمات پس از فروش هم در استفاده روزمره مهم‌اند. اعداد و ادعاهای عملکردی را از مشخصات رسمی همان محصول بررسی کنید.</p>
                    </div>
                    <a href="{{ route('categories.show', 'hoods') }}" class="mt-6 inline-flex text-sm font-semibold text-[var(--livora-accent)] hover:text-[var(--livora-text)]">دیدن هودهای آشپزخانه <span class="mr-2">←</span></a>
                </article>

                <article id="sink-guide" class="scroll-mt-28 rounded-[1.75rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-9">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--livora-accent)]">GUIDE 02 / SINKS</p>
                    <h2 class="mt-3 text-2xl font-semibold sm:text-3xl">چطور سینک ظرفشویی مناسب انتخاب کنیم؟</h2>
                    <div class="mt-5 space-y-4 text-sm leading-8 text-[var(--livora-stone)]">
                        <p>از اندازه محل برش کابینت شروع کنید؛ طول، عرض و شکل نصب سینک باید با صفحه کابینت و فضای زیر آن سازگار باشد. ابعاد بیرونی محصول به‌تنهایی برای تصمیم کافی نیست، پس اندازه برش موردنیاز را هم بررسی کنید.</p>
                        <p>جنس و پرداخت سطح، تعداد و عمق لگن‌ها، محل سینی، جهت نصب، سازگاری با شیرآلات و فضای موردنیاز برای اتصالات را با عادت‌های روزانه خود بسنجید. در آشپزخانه کوچک، فضای کاری و مسیر حرکت نیز اهمیت دارد.</p>
                        <p>روش نظافت پیشنهادی سازنده و شرایط گارانتی را بخوانید. برای جلوگیری از اشتباه در نصب، ابعاد دقیق و اقلام همراه را از صفحه محصول یا راهنمای رسمی آن کنترل کنید.</p>
                    </div>
                    <a href="{{ route('categories.show', 'sinks') }}" class="mt-6 inline-flex text-sm font-semibold text-[var(--livora-accent)] hover:text-[var(--livora-text)]">دیدن سینک‌های ظرفشویی <span class="mr-2">←</span></a>
                </article>

                <article id="measure-guide" class="scroll-mt-28 rounded-[1.75rem] border border-[var(--livora-border)] bg-[var(--livora-white)] p-6 sm:p-9">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--livora-accent)]">GUIDE 03 / CHECKLIST</p>
                    <h2 class="mt-3 text-2xl font-semibold sm:text-3xl">چک‌لیست ساده پیش از ثبت سفارش</h2>
                    <ul class="mt-5 space-y-3 text-sm leading-8 text-[var(--livora-stone)]">
                        <li>• ابعاد محل نصب و فضای خالی کابینت را دقیق اندازه بگیرید.</li>
                        <li>• نوع نصب و الزامات سازنده را برای مدل موردنظر بررسی کنید.</li>
                        <li>• برای هود، مسیر خروج هوا و شرایط کانال‌کشی را در نظر بگیرید.</li>
                        <li>• برای سینک، محل شیرآلات، خروجی آب و فضای اتصالات را بررسی کنید.</li>
                        <li>• مشخصات، اقلام همراه، گارانتی و روش خدمات را پیش از پرداخت بخوانید.</li>
                    </ul>
                    <p class="mt-5 text-sm leading-8 text-[var(--livora-stone)]">اگر درباره سازگاری محصول با فضای آشپزخانه مطمئن نیستید، قبل از سفارش از نصاب یا پشتیبانی راهنمایی بگیرید.</p>
                </article>
            </div>
        </x-layout.container>
    </section>

    <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">
        <x-layout.container>
            <div class="flex flex-col gap-5 py-10 sm:flex-row sm:items-center sm:justify-between sm:py-14">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[var(--livora-accent)]">NEXT STEP</p>
                    <h2 class="mt-2 text-2xl font-semibold">حالا مدل مناسب را بررسی کنید.</h2>
                    <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">راهنما را با مشخصات هر محصول کنار هم قرار دهید و انتخاب نهایی را بر اساس فضای واقعی خود انجام دهید.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('categories.index') }}" class="inline-flex items-center rounded-2xl bg-[var(--livora-accent)] px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-[var(--livora-accent-dark)]">مشاهده دسته‌بندی‌ها</a>
                    <a href="{{ route('contact') }}" class="inline-flex items-center rounded-2xl border border-[var(--livora-border-dark)] bg-[var(--livora-white)] px-5 py-3.5 text-sm font-semibold text-[var(--livora-text)] transition hover:border-[var(--livora-accent)]">مشاوره و تماس</a>
                </div>
            </div>
        </x-layout.container>
    </section>
</div>
@endsection
