@props([
'categories' => collect(),
'selectedCategory' => null,
])

<div
    x-show="filterOpen"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[110] lg:hidden"
>

    {{-- =========================================================
         BACKDROP
    ========================================================== --}}

    <button
        type="button"
        aria-label="بستن فیلترها"
        @click="filterOpen = false"
        class="absolute inset-0 bg-black/35 backdrop-blur-sm"
    ></button>


    {{-- =========================================================
         DRAWER
    ========================================================== --}}

    <aside
        x-show="filterOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 flex h-full w-[88%] max-w-md flex-col bg-[var(--livora-cream)] shadow-2xl"
    >

        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <div
            class="flex h-[76px] shrink-0 items-center justify-between border-b border-[var(--livora-border)] bg-[var(--livora-white)] px-5"
        >

            <div>

                <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--livora-accent)]">
                    FILTERS
                </p>

                <h2 class="mt-1 text-sm font-semibold">
                    فیلتر محصولات
                </h2>

            </div>


            <button
                type="button"
                aria-label="بستن"
                @click="filterOpen = false"
                class="flex h-10 w-10 items-center justify-center rounded-full text-[var(--livora-ink)] transition hover:bg-[var(--livora-surface)]"
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


        {{-- =====================================================
             MOBILE FILTER FORM
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('shop.index') }}"
            class="flex min-h-0 flex-1 flex-col"
        >

            {{-- =================================================
                 SCROLL AREA
            ================================================== --}}

            <div class="min-h-0 flex-1 overflow-y-auto px-5 py-6">

                <x-shop.filters
                    mobile
                    :categories="$categories"
                    :selected-category="$selectedCategory"
                />

            </div>


            {{-- =================================================
                 BOTTOM ACTIONS
            ================================================== --}}

            <div
                class="shrink-0 border-t border-[var(--livora-border)] bg-[var(--livora-white)] p-5"
            >

                <div class="grid grid-cols-2 gap-3">

                    {{-- CLEAR --}}

                    <a
                        href="{{ route('shop.index') }}"
                        @click="filterOpen = false"
                        class="flex items-center justify-center rounded-2xl border border-[var(--livora-border)] px-4 py-3.5 text-xs font-medium text-[var(--livora-ink)] transition hover:border-[var(--livora-ink)]"
                    >
                        پاک کردن
                    </a>


                    {{-- APPLY --}}

                    <button
                        type="submit"
                        @click="filterOpen = false"
                        class="rounded-2xl bg-[var(--livora-ink)] px-4 py-3.5 text-xs font-medium text-white transition hover:bg-[var(--livora-accent)]"
                    >
                        اعمال فیلتر
                    </button>

                </div>

            </div>

        </form>

    </aside>

</div>
