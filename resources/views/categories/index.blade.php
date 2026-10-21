@extends('layouts.app')

@section('title', 'دسته‌بندی هود و سینک | فرزین')

@section(
    'description',
    'دسته‌بندی‌های هود آشپزخانه و سینک ظرفشویی فرزین را ببینید و مدل مناسب فضای نصب خود را پیدا کنید.'
)

@section('canonical', route('categories.index'))



@section('content')

    <div class="overflow-hidden bg-[var(--livora-cream)]">

        {{-- HERO --}}
        <section class="fz-category-hero border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="fz-category-hero__inner py-6 sm:py-8 lg:py-10">

                    <nav
                        aria-label="breadcrumb"
                        class="flex items-center gap-2 text-[11px] text-[var(--livora-stone)]"
                    >
                        <a
                            href="{{ route('home') }}"
                            class="transition hover:text-[var(--livora-text)]"
                        >
                            خانه
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-text)]">
                        دسته‌بندی‌ها
                    </span>
                    </nav>

                    <div class="fz-category-hero__layout">
                        <div class="fz-category-hero__content">
                            <p class="fz-category-hero__eyebrow">SHOP BY CATEGORY / FIND YOUR FIT</p>

                            <h1 class="fz-category-hero__title">
                                فضای مناسب خودت را پیدا کن.
                            </h1>

                            <p class="fz-category-hero__description">
                                از هود آشپزخانه تا سینک ظرفشویی؛ مجموعه‌های فرزین را بر اساس فرم، ابعاد و فضای نصب بررسی کن.
                                طراحی دقیق، متریال بادوام و انتخابی هماهنگ با آشپزخانه‌ات.
                            </p>

                            <div class="fz-category-hero__actions">
                                <a href="#category-list" class="fz-category-hero__action">
                                    <span>انتخاب دسته‌بندی</span><span aria-hidden="true">↙</span>
                                </a>
                                <span class="fz-category-hero__hint">FARZIN / DESIGN SYSTEMS</span>
                            </div>
                        </div>

                        <div class="fz-category-hero__visual" data-category-hero aria-label="نمایش گرافیکی هود و سینک فرزین">
                            <span class="fz-category-hero__orbit fz-category-hero__orbit--outer" aria-hidden="true"></span>
                            <span class="fz-category-hero__orbit fz-category-hero__orbit--inner" aria-hidden="true"></span>
                            <div class="fz-category-hero__stamp">FARZIN <span>DESIGN STUDIO / 2026</span></div>
                            <span class="fz-category-hero__spec" aria-hidden="true">METAL · FORM · FUNCTION</span>

                            <article class="fz-category-hero__product fz-category-hero__product--hood" data-category-float>
                                <div class="fz-category-hero__product-art" aria-hidden="true">
                                    <svg viewBox="0 0 280 130" fill="none">
                                        <defs>
                                            <linearGradient id="fzHoodMetal" x1="26" y1="18" x2="244" y2="124" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#F5F3EF"/>
                                                <stop offset=".42" stop-color="#B8BDC4"/>
                                                <stop offset=".75" stop-color="#555A61"/>
                                                <stop offset="1" stop-color="#E57852"/>
                                            </linearGradient>
                                        </defs>
                                        <path d="M94 12h92v35l25 18v18H69V65l25-18V12Z" fill="url(#fzHoodMetal)" fill-opacity=".96" stroke="#F5F3EF" stroke-opacity=".8" stroke-width="1.2"/>
                                        <path d="M80 84h120v12H80z" fill="#111316" stroke="#C7CBD0" stroke-opacity=".8"/>
                                        <path d="M91 88h98M95 92h90M99 96h82" stroke="#77777D" stroke-width="1.7" stroke-linecap="round"/>
                                        <path d="M108 19h64M108 25h64" stroke="#fff" stroke-opacity=".45" stroke-width="1.4" stroke-linecap="round"/>
                                        <path d="M112 102v7m14-7v7m14-7v7m14-7v7m14-7v7" stroke="#F0B568" stroke-opacity=".85" stroke-width="1.5" stroke-linecap="round"/>
                                        <path d="M42 116h196" stroke="#C7CBD0" stroke-opacity=".25" stroke-width="1"/>
                                    </svg>
                                </div>
                                <div class="fz-category-hero__product-label">
                                    <small>AIR / 01</small>
                                    <strong>هود آشپزخانه</strong>
                                    <em>POWER · PRECISION</em>
                                </div>
                                <span class="fz-category-hero__index">01</span>
                            </article>

                            <article class="fz-category-hero__product fz-category-hero__product--sink" data-category-float>
                                <div class="fz-category-hero__product-art" aria-hidden="true">
                                    <svg viewBox="0 0 280 130" fill="none">
                                        <defs>
                                            <linearGradient id="fzSinkMetal" x1="35" y1="12" x2="243" y2="118" gradientUnits="userSpaceOnUse">
                                                <stop stop-color="#FAF9F6"/>
                                                <stop offset=".5" stop-color="#AEB4BB"/>
                                                <stop offset=".82" stop-color="#555A61"/>
                                                <stop offset="1" stop-color="#F0B568"/>
                                            </linearGradient>
                                        </defs>
                                        <rect x="42" y="20" width="196" height="86" rx="13" fill="url(#fzSinkMetal)" stroke="#FCFBF8" stroke-opacity=".8" stroke-width="1.2"/>
                                        <rect x="55" y="31" width="170" height="64" rx="8" fill="#292D32" stroke="#D8DCE0" stroke-opacity=".75" stroke-width="1.2"/>
                                        <rect x="68" y="41" width="144" height="44" rx="5" fill="#202328" stroke="#77777D" stroke-width="1"/>
                                        <path d="M140 41v44" stroke="#90959B" stroke-opacity=".6"/>
                                        <circle cx="201" cy="50" r="3" fill="#F0B568"/>
                                        <path d="M182 20V10c0-5-4-8-9-8h-9" stroke="#F5F3EF" stroke-width="5" stroke-linecap="round"/>
                                        <path d="M164 2h-7" stroke="#C7CBD0" stroke-width="5" stroke-linecap="round"/>
                                        <path d="M73 113h134" stroke="#C7CBD0" stroke-opacity=".3"/>
                                    </svg>
                                </div>
                                <div class="fz-category-hero__product-label">
                                    <small>FORM / 02</small>
                                    <strong>سینک ظرفشویی</strong>
                                    <em>CRAFT · DURABILITY</em>
                                </div>
                                <span class="fz-category-hero__index">02</span>
                            </article>
                            <span class="fz-category-hero__lightline" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>

            </x-layout.container>

        </section>


        {{-- CATEGORIES --}}
        <section id="category-list" class="fz-category-list">

            <x-layout.container>

                @if($categories->isNotEmpty())

                    <div class="py-8 sm:py-10 lg:py-12">

                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                            @foreach($categories as $category)

                                <x-shop.category-card
                                    :name="$category->name"
                                    :image="$category->image_url"
                                    :motion-media="$category->hoverMedia"
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
