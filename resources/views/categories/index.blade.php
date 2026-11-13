@extends('layouts.app')

@section('title', 'دسته‌بندی تجهیزات آشپزخانه | فرجی‌هوم')
@section('description', 'دسته‌های فعال فرجی‌هوم را ببینید؛ هود، سینک و تجهیزات آشپزخانه را بر اساس نیاز، فضای نصب و مشخصات هر مدل انتخاب و مقایسه کنید.')
@section('canonical', route('categories.index'))
@section('body_class', 'faraji-categories-page min-h-screen antialiased')

@section('content')
<div class="overflow-hidden bg-[var(--livora-cream)]">
    <section class="relative overflow-hidden border-b border-[var(--livora-border)] bg-[var(--livora-ink)] text-white">
        <div aria-hidden="true" class="pointer-events-none absolute -left-24 -top-28 h-80 w-80 rounded-full bg-[var(--livora-accent)]/20 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute bottom-0 right-0 h-px w-full bg-gradient-to-l from-transparent via-white/25 to-transparent"></div>

        <x-layout.container>
            <div class="relative py-10 sm:py-14 lg:py-20">
                <nav aria-label="مسیر صفحه" class="flex flex-wrap items-center gap-2 text-xs text-white/60">
                    <a href="{{ route('home') }}" class="transition hover:text-white">خانه</a>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page" class="text-white">دسته‌بندی محصولات</span>
                </nav>

                <div class="mt-10 grid items-end gap-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(280px,.85fr)] lg:gap-16">
                    <div class="max-w-3xl">
                        <p class="text-xs font-bold tracking-wide text-white/65">فرجی‌هوم / راهنمای انتخاب</p>
                        <h1 class="mt-5 text-4xl font-black leading-[1.45] tracking-tight text-white sm:text-5xl lg:text-6xl">
                            دسته مناسب را پیدا کنید؛
                            <span class="block text-[#e6b8a6]">بعد با خیال راحت مقایسه کنید.</span>
                        </h1>
                        <p class="mt-6 max-w-2xl text-sm leading-9 text-white/75 sm:text-base">
                            برای انتخاب تجهیزات آشپزخانه، از گروه محصول شروع کنید. مدل‌ها را بر اساس نوع نصب، ابعاد، جنس و ویژگی‌های فنی بررسی کنید و مشخصات هر کالا را پیش از سفارش بخوانید.
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="#category-list" class="inline-flex min-h-12 items-center justify-center gap-3 bg-white px-6 py-3 text-sm font-bold text-[var(--livora-ink)] transition hover:bg-[#f3e9e5]">
                                انتخاب دسته‌بندی <span aria-hidden="true">↓</span>
                            </a>
                            <a href="{{ route('contact') }}" class="inline-flex min-h-12 items-center justify-center border border-white/35 px-6 py-3 text-sm font-semibold text-white transition hover:border-white hover:bg-white/10">
                                مشاوره پیش از خرید
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-px border border-white/15 bg-white/15">
                        <div class="min-h-36 bg-white/[0.06] p-5 sm:p-6">
                            <span class="text-xs text-white/55">گام اول</span>
                            <strong class="mt-4 block text-lg font-bold text-white sm:text-xl">نیازسنجی</strong>
                            <p class="mt-2 text-xs leading-7 text-white/65">نوع محصول و فضای نصب را مشخص کنید.</p>
                        </div>
                        <div class="min-h-36 bg-white/[0.06] p-5 sm:p-6">
                            <span class="text-xs text-white/55">گام دوم</span>
                            <strong class="mt-4 block text-lg font-bold text-white sm:text-xl">مقایسه</strong>
                            <p class="mt-2 text-xs leading-7 text-white/65">مشخصات، قیمت و موجودی مدل‌ها را ببینید.</p>
                        </div>
                        <div class="col-span-2 flex items-center justify-between gap-4 bg-white/[0.06] p-5 sm:p-6">
                            <div>
                                <span class="text-xs text-white/55">همراه انتخاب شما</span>
                                <strong class="mt-2 block text-base font-bold text-white">فرجی‌هوم</strong>
                            </div>
                            <span class="text-3xl font-light text-[#e6b8a6]" aria-hidden="true">01—02</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-layout.container>
    </section>

    <section id="category-list" class="scroll-mt-28">
        <x-layout.container>
            <div class="py-12 sm:py-16">
                <header class="mb-8 flex flex-col gap-4 border-b border-[var(--livora-border)] pb-6 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-bold text-[var(--livora-accent)]">دسته‌بندی‌های فرجی‌هوم</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-[var(--livora-ink)] sm:text-3xl">محصولات را بر اساس دسته ببینید</h2>
                        <p class="mt-3 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">وارد دسته موردنظرتان شوید تا توضیحات کاربردی، محصولات و مشخصات مدل‌ها را یک‌جا بررسی کنید.</p>
                    </div>
                    <a href="{{ route('shop.index') }}" class="inline-flex min-h-11 w-fit items-center gap-2 border border-[var(--livora-border)] px-5 py-3 text-sm font-semibold text-[var(--livora-ink)] transition hover:border-[var(--livora-accent)] hover:text-[var(--livora-accent)]">
                        مشاهده همه محصولات <span aria-hidden="true">←</span>
                    </a>
                </header>

                @if($categories->isNotEmpty())
                    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach($categories as $category)
                            <x-shop.category-card
                                :name="$category->name"
                                :image="$category->image_url"
                                :image-srcset="$category->image_srcset"
                                :motion-media="$category->hoverMedia"
                                :href="route('categories.show', $category->slug)"
                            />
                        @endforeach
                    </div>

                    @if(method_exists($categories, 'hasPages') && $categories->hasPages())
                        <div class="mt-10">{{ $categories->withQueryString()->links() }}</div>
                    @endif
                @else
                    <div class="border border-[var(--livora-border)] bg-white px-6 py-20 text-center">
                        <p class="text-xs font-bold text-[var(--livora-accent)]">فهرست دسته‌بندی‌ها در حال تکمیل است</p>
                        <h2 class="mt-3 text-2xl font-black text-[var(--livora-ink)]">هنوز دسته فعالی برای نمایش وجود ندارد.</h2>
                        <p class="mx-auto mt-3 max-w-lg text-sm leading-8 text-[var(--livora-stone)]">برای دریافت اطلاعات محصولات موجود یا راهنمایی درباره انتخاب، با تیم فرجی‌هوم در ارتباط باشید.</p>
                        <a href="{{ route('contact') }}" class="mt-7 inline-flex min-h-12 items-center justify-center bg-[var(--livora-ink)] px-6 py-3 text-sm font-bold text-white transition hover:bg-[var(--livora-accent)]">ارتباط با ما</a>
                    </div>
                @endif
            </div>
        </x-layout.container>
    </section>

    <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">
        <x-layout.container>
            <div class="flex flex-col gap-6 py-10 sm:flex-row sm:items-center sm:justify-between sm:py-14">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold text-[var(--livora-accent)]">هنوز مطمئن نیستید؟</p>
                    <h2 class="mt-2 text-2xl font-black text-[var(--livora-ink)]">قبل از خرید، سؤال‌هایتان را بپرسید.</h2>
                    <p class="mt-2 text-sm leading-8 text-[var(--livora-stone)]">برای بررسی تناسب مدل با ابعاد و نیاز آشپزخانه‌تان، با فرجی‌هوم تماس بگیرید.</p>
                </div>
                <a href="{{ route('contact') }}" class="inline-flex min-h-12 w-fit items-center justify-center bg-[var(--livora-ink)] px-7 py-3 text-sm font-bold text-white transition hover:bg-[var(--livora-accent)]">تماس با فرجی‌هوم</a>
            </div>
        </x-layout.container>
    </section>
</div>
@endsection
