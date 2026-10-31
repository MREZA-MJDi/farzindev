@extends('layouts.app')

@section('title', 'علاقه‌مندی‌های من | فرزین')

@section(
    'description',
    'محصولات مورد علاقه خود را در فرزین ذخیره کنید و هر زمان برای خرید به آن‌ها برگردید.'
)

@push('seo')
    <meta
        name="robots"
        content="noindex,nofollow,noarchive"
    >
@endpush

@section('content')

    @php
        $wishlistItems = $products;
    @endphp

    <div class="min-h-[70vh] bg-[var(--livora-cream)]">

        {{-- =========================================================
             HEADER
        ========================================================== --}}
        <section class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="py-8 sm:py-12">

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
                            href="{{ route('account.index') }}"
                            class="transition hover:text-[var(--livora-text)]"
                        >
                            حساب کاربری
                        </a>

                        <span>/</span>

                        <span class="text-[var(--livora-text)]">
                        علاقه‌مندی‌ها
                    </span>

                    </nav>

                    <div class="mt-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                SAVED ITEMS
                            </p>

                            <h1 class="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">
                                علاقه‌مندی‌های من
                            </h1>

                            <p class="mt-3 max-w-2xl text-sm leading-8 text-[var(--livora-stone)]">
                                محصولاتی که برای بعد ذخیره کرده‌ای را اینجا پیدا کن.
                            </p>

                        </div>

                        <a
                            href="{{ route('shop.index') }}"
                            class="inline-flex w-fit items-center rounded-2xl bg-[var(--livora-ink)] px-5 py-3.5 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                        >
                            ادامه خرید
                        </a>

                    </div>

                </div>

            </x-layout.container>

        </section>


        {{-- =========================================================
             CONTENT
        ========================================================== --}}
        <section>

            <x-layout.container>

                @if($wishlistItems->isEmpty())

                    {{-- Empty --}}
                    <div class="py-20 sm:py-28">

                        <div class="mx-auto max-w-xl text-center">

                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[1.75rem] bg-[var(--livora-white)] text-[var(--livora-stone)]">

                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.35"
                                    stroke="currentColor"
                                    class="h-8 w-8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21.75 8.25c0 6-9.75 11.25-9.75 11.25S2.25 14.25 2.25 8.25A5.25 5.25 0 0 1 12 5.58a5.25 5.25 0 0 1 9.75 2.67Z"
                                    />
                                </svg>

                            </div>

                            <p class="mt-7 text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                                NOTHING SAVED
                            </p>

                            <h2 class="mt-3 text-3xl font-semibold tracking-tight">
                                هنوز چیزی ذخیره نکرده‌ای
                            </h2>

                            <p class="mx-auto mt-4 max-w-md text-sm leading-8 text-[var(--livora-stone)]">
                                محصولاتی که دوست داری را ذخیره کن تا بعداً راحت‌تر به آن‌ها برگردی.
                            </p>

                            <a
                                href="{{ route('shop.index') }}"
                                class="mt-8 inline-flex items-center rounded-2xl bg-[var(--livora-ink)] px-7 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                            >
                                کشف محصولات
                            </a>

                        </div>

                    </div>

                @else

                    <div class="py-8 sm:py-10">

                        {{-- Toolbar --}}
                        <div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                            <div>

                                <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                                    MY PICKS
                                </p>

                                <p class="mt-2 text-sm text-[var(--livora-stone)]">
                                    {{ number_format($wishlistItems->total()) }}
                                    محصول ذخیره شده
                                </p>

                            </div>

                            <a
                                href="{{ route('shop.index') }}"
                                class="text-sm font-medium text-[var(--livora-accent)]"
                            >
                                مشاهده فروشگاه
                                <span class="mr-1">←</span>
                            </a>

                        </div>


                        {{-- Products --}}
                        <div class="grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                            @foreach($wishlistItems as $product)

                                <div class="group relative min-w-0">

                                    {{-- Product --}}
                                    <x-product.card
                                        :product="$product"
                                    />

                                    {{-- Remove --}}
                                    <form
                                            action="{{ route('account.wishlist.destroy', $product) }}"
                                            method="POST"
                                            class="absolute right-3 top-3 z-20"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                aria-label="حذف از علاقه‌مندی‌ها"
                                                onclick="return confirm('این محصول از علاقه‌مندی‌ها حذف شود؟')"
                                                class="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-black/35 text-white shadow-sm backdrop-blur-xl transition hover:bg-red-600"
                                            >

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="currentColor"
                                                    viewBox="0 0 24 24"
                                                    class="h-4 w-4"
                                                >
                                                    <path
                                                        d="M6.225 4.811 12 10.586l5.775-5.775 1.414 1.414L13.414 12l5.775 5.775-1.414 1.414L12 13.414l-5.775 5.775-1.414-1.414L10.586 12 4.811 6.225z"
                                                    />
                                                </svg>

                                            </button>

                                        </form>

                                </div>

                            @endforeach

                        </div>

                    </div>

                @endif

            </x-layout.container>

        </section>


        {{-- =========================================================
             CTA
        ========================================================== --}}
        @if($wishlistItems->isNotEmpty())

            <section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">

                <x-layout.container>

                    <div class="flex flex-col gap-6 py-12 sm:flex-row sm:items-center sm:justify-between sm:py-16">

                        <div>

                            <p class="text-[10px] uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                                KEEP EXPLORING
                            </p>

                            <h2 class="mt-2 text-2xl font-semibold">
                                شاید انتخاب بعدی‌ات همین نزدیکی باشد.
                            </h2>

                            <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">
                                مجموعه کامل فرزین را بررسی کن.
                            </p>

                        </div>

                        <a
                            href="{{ route('shop.index') }}"
                            class="inline-flex w-fit rounded-2xl bg-[var(--livora-ink)] px-6 py-4 text-sm font-medium text-white transition hover:bg-[var(--livora-accent)]"
                        >
                            رفتن به فروشگاه
                        </a>

                    </div>

                </x-layout.container>

            </section>

        @endif

    </div>

@endsection
