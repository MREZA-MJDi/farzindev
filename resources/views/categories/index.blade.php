@extends('layouts.app')

@section('title', 'دسته‌بندی هود و سینک | فرزین')

@section(
    'description',
    'دسته‌بندی‌های هود آشپزخانه و سینک ظرفشویی فرزین را ببینید و مدل مناسب فضای نصب خود را پیدا کنید.'
)

@section('canonical', route('categories.index'))

@push('seo')
    <meta
        name="robots"
        content="index,follow"
    >

    <meta
        property="og:type"
        content="website"
    >

    <meta
        property="og:title"
        content="دسته‌بندی هود و سینک | فرزین"
    >

    <meta
        property="og:description"
        content="دسته‌بندی‌های هود آشپزخانه و سینک ظرفشویی فرزین."
    >

    <meta
        property="og:url"
        content="{{ route('categories.index') }}"
    >
@endpush

@section('content')

    <div class="overflow-hidden bg-[var(--livora-cream)]">

        {{-- HERO --}}
        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-10 sm:py-16 lg:py-20">

                    <nav
                        aria-label="breadcrumb"
                        class="flex items-center gap-2 text-[11px] text-[var(--livora-stone)]"
                    >
                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--livora-ink)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-ink)]">
                        دسته‌بندی‌ها
                    </span>
                    </nav>

                    <div class="mt-10 max-w-3xl">

                        <p class="text-[10px] font-medium uppercase tracking-[0.24em] text-[var(--livora-accent)]">
                            SHOP BY CATEGORY
                        </p>

                        <h1 class="mt-4 text-4xl font-semibold tracking-tight sm:text-5xl lg:text-6xl">
                            فضای مناسب خودت را پیدا کن.
                        </h1>

                        <p class="mt-5 max-w-2xl text-sm leading-8 text-[var(--livora-stone)] sm:text-base">
                            از هود آشپزخانه تا سینک ظرفشویی؛ دسته‌بندی‌ها را بر اساس نیاز و فضای نصب بررسی کنید.
                            مجموعه‌های فرزین را بر اساس فضای موردنظر بررسی کن.
                        </p>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- CATEGORIES --}}
        <section>

            <x-layout.container>

                @if($categories->isNotEmpty())

                    <div class="py-10 sm:py-14 lg:py-16">

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                            @foreach($categories as $category)

                                <x-shop.category-card
                                    :name="$category->name"
                                    :image="$category->image_url"
                                    :href="route('categories.show', $category->slug)"
                                    :count="$category->products_count"
                                />

                            @endforeach

                        </div>

                        @if(method_exists($categories, 'hasPages') && $categories->hasPages())

                            <div class="mt-10">
                                {{ $categories->withQueryString()->links() }}
                            </div>

                        @endif

                    </div>

                @else

                    <div class="py-24 text-center">

                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[1.75rem] bg-[var(--livora-white)]">
                        <span class="text-xs tracking-[0.2em] text-[var(--livora-stone)]">
                            FZ
                        </span>
                        </div>

                        <p class="mt-7 text-[10px] uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            EMPTY
                        </p>

                        <h2 class="mt-3 text-3xl font-semibold">
                            هنوز دسته‌بندی‌ای ثبت نشده است.
                        </h2>

                        <p class="mx-auto mt-4 max-w-md text-sm leading-8 text-[var(--livora-stone)]">
                            دسته‌بندی‌ها پس از ثبت در پنل مدیریت در این بخش نمایش داده می‌شوند.
                        </p>

                        <a
                            href="{{ route('shop.index') }}"
                            class="mt-8 inline-flex rounded-2xl bg-[var(--livora-ink)] px-7 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                        >
                            مشاهده فروشگاه
                        </a>

                    </div>

                @endif

            </x-layout.container>

        </section>


        {{-- CTA --}}
        <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">

            <x-layout.container>

                <div class="flex flex-col gap-6 py-12 sm:flex-row sm:items-center sm:justify-between sm:py-16">

                    <div>

                        <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                            فرزین
                        </p>

                        <h2 class="mt-2 text-2xl font-semibold">
                            چیزی که دنبالش هستی، شاید همین‌جا باشد.
                        </h2>

                        <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">
                            همه محصولات را هم می‌توانی یکجا بررسی کنی.
                        </p>

                    </div>

                    <a
                        href="{{ route('shop.index') }}"
                        class="inline-flex w-fit rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                    >
                        ورود به فروشگاه
                    </a>

                </div>

            </x-layout.container>

        </section>

    </div>

@endsection
