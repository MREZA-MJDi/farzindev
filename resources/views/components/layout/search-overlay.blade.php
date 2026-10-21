<div
    x-show="searchOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[110]"
>

    {{-- Backdrop --}}
    <button
        type="button"
        aria-label="بستن جستجو"
        @click="searchOpen = false"
        class="absolute inset-0 bg-black/40 backdrop-blur-md"
    ></button>


    {{-- Search Panel --}}
    <div
        x-show="searchOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-y-4 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="-translate-y-4 opacity-0"
        class="relative mx-auto w-full max-w-4xl overflow-hidden rounded-b-[2rem] bg-[var(--livora-cream)] shadow-2xl"
    >

        {{-- Header --}}
        <div class="border-b border-[var(--livora-border)] bg-[var(--livora-white)]">

            <x-layout.container>

                <div class="flex h-[76px] items-center justify-between gap-4">

                    <div>

                        <p class="text-[10px] font-medium uppercase tracking-[0.22em] text-[var(--livora-accent)]">
                            LIVORA SEARCH
                        </p>

                        <p class="mt-1 text-xs text-[var(--livora-stone)]">
                            محصول، دسته‌بندی یا عبارت موردنظر را جستجو کنید.
                        </p>

                    </div>

                    <button
                        type="button"
                        aria-label="بستن جستجو"
                        @click="searchOpen = false"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[var(--livora-ink)] transition hover:bg-[var(--livora-surface)]"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18 18 6M6 6l12 12"
                            />
                        </svg>

                    </button>

                </div>

            </x-layout.container>

        </div>


        {{-- Search Form --}}
        <x-layout.container>

            <div class="py-7 sm:py-9">

                @php
                    $searchResetQuery = request()->except(['search', 'page']);
                    $searchResetUrl = route('shop.index')
                        . ($searchResetQuery
                            ? '?' . http_build_query($searchResetQuery)
                            : '');
                @endphp

                <form
                    action="{{ route('shop.index') }}"
                    method="GET"
                    class="relative"
                    x-data="{ searchValue: @js((string) request('search', '')) }"
                    @submit.prevent="
                        const value = searchValue.trim();
                        const url = new URL(window.location.href);

                        url.pathname = @js(parse_url(route('shop.index'), PHP_URL_PATH));
                        url.searchParams.delete('search');
                        url.searchParams.delete('page');

                        if (value) {
                            url.searchParams.set('search', value);
                        }

                        window.location.assign(
                            url.pathname + (url.search ? url.search : '')
                        );
                    "
                >

                    <label
                        for="global-search"
                        class="sr-only"
                    >
                        جستجو
                    </label>

                    <input
                        x-ref="searchInput"
                        id="global-search"
                        name="search"
                        type="search"
                        x-model="searchValue"
                        autocomplete="off"
                        placeholder="مثلاً: مبل راحتی، میز ناهارخوری، مبل چستر..."
                        class="w-full rounded-[1.5rem] border border-[var(--livora-border)] bg-[var(--livora-white)] px-14 py-5 text-sm text-[var(--livora-ink)] outline-none transition placeholder:text-[var(--livora-stone)] focus:border-[var(--livora-ink)]"
                    >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="pointer-events-none absolute right-5 top-1/2 h-5 w-5 -translate-y-1/2 text-[var(--livora-stone)]"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z"
                        />
                    </svg>

                    <div class="absolute left-2 top-1/2 flex -translate-y-1/2 items-center gap-2">
                        <a
                            href="{{ $searchResetUrl }}"
                            x-show="searchValue.length > 0"
                            x-cloak
                            aria-label="پاک کردن جستجو"
                            title="پاک کردن جستجو"
                            class="flex h-10 w-10 items-center justify-center rounded-xl text-[var(--livora-stone)] transition hover:bg-[var(--livora-surface)] hover:text-[var(--livora-ink)]"
                            @click="searchValue = ''"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.7"
                                stroke="currentColor"
                                class="h-4 w-4"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 6l12 12M18 6 6 18"
                                />
                            </svg>
                        </a>

                        <button
                            type="submit"
                            class="rounded-2xl bg-[var(--livora-ink)] px-5 py-3 text-xs font-medium text-white transition hover:bg-[var(--livora-accent)]"
                        >
                            جستجو
                        </button>
                    </div>

                </form>


                {{-- Quick Links --}}
                <div class="mt-8">

                    <div class="flex items-center justify-between gap-4">

                        <div>

                            <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                                QUICK DISCOVER
                            </p>

                            <p class="mt-1 text-xs text-[var(--livora-stone)]">
                                جستجو را از دسته‌بندی‌های محبوب شروع کنید.
                            </p>

                        </div>

                        <a
                            href="{{ route('categories.index') }}"
                            @click="searchOpen = false"
                            class="text-xs font-medium text-[var(--livora-accent)]"
                        >
                            همه دسته‌بندی‌ها
                        </a>

                    </div>


                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">

                        <a
                            href="{{ route('shop.index', ['search' => 'مبل']) }}"
                            @click="searchOpen = false"
                            class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4 transition hover:border-[var(--livora-ink)]"
                        >

                            <p class="text-sm font-medium">
                                مبل
                            </p>

                            <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                Sofa
                            </p>

                        </a>


                        <a
                            href="{{ route('shop.index', ['search' => 'میز']) }}"
                            @click="searchOpen = false"
                            class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4 transition hover:border-[var(--livora-ink)]"
                        >

                            <p class="text-sm font-medium">
                                میز
                            </p>

                            <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                Tables
                            </p>

                        </a>


                        <a
                            href="{{ route('shop.index', ['search' => 'صندلی']) }}"
                            @click="searchOpen = false"
                            class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4 transition hover:border-[var(--livora-ink)]"
                        >

                            <p class="text-sm font-medium">
                                صندلی
                            </p>

                            <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                Chairs
                            </p>

                        </a>


                        <a
                            href="{{ route('shop.index', ['search' => 'تخت']) }}"
                            @click="searchOpen = false"
                            class="rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-4 transition hover:border-[var(--livora-ink)]"
                        >

                            <p class="text-sm font-medium">
                                تخت
                            </p>

                            <p class="mt-1 text-[11px] text-[var(--livora-stone)]">
                                Bedroom
                            </p>

                        </a>

                    </div>

                </div>


                {{-- SEO / Discovery Hint --}}
                <div class="mt-8 rounded-3xl bg-[var(--livora-ink)] p-5 text-white sm:p-6">

                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                        <div>

                            <p class="text-[10px] uppercase tracking-[0.2em] text-white/40">
                                FIND YOUR SPACE
                            </p>

                            <h2 class="mt-3 text-lg font-semibold">
                                دنبال چه سبکی هستی؟
                            </h2>

                            <p class="mt-2 max-w-xl text-xs leading-7 text-white/50">
                                عبارت‌هایی مثل «مبل مدرن»، «مبل چستر»،
                                «مبل راحتی» یا نام مدل را جستجو کن.
                            </p>

                        </div>

                        <a
                            href="{{ route('shop.index') }}"
                            @click="searchOpen = false"
                            class="inline-flex w-fit shrink-0 rounded-2xl bg-white px-5 py-3 text-xs font-medium text-[var(--livora-ink)] transition hover:bg-[var(--livora-cream)]"
                        >
                            مشاهده فروشگاه
                        </a>

                    </div>

                </div>

            </div>

        </x-layout.container>

    </div>

</div>
