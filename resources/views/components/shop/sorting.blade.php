@props([
'products' => null,
])

@php
    $currentSort = request('sort', 'latest');

    $sortOptions = [
        'latest' => 'جدیدترین',
        'oldest' => 'قدیمی‌ترین',
        'price_asc' => 'ارزان‌ترین',
        'price_desc' => 'گران‌ترین',
        'name_asc' => 'الفبایی',
    ];

    $currentLabel =
        $sortOptions[$currentSort]
        ?? $sortOptions['latest'];

    /*
     |--------------------------------------------------------------------------
     | Preserve Existing Query
     |--------------------------------------------------------------------------
     */

    $baseQuery = request()->except('sort');
@endphp

<div
    x-data="{
        open: false,
        value: @js($currentSort),
        label: @js($currentLabel)
    }"
    @click.outside="open = false"
    class="relative"
>

    {{-- =========================================================
         Desktop / Main Trigger
    ========================================================== --}}
    <button
        type="button"
        @click="open = !open"
        :aria-expanded="open.toString()"
        aria-haspopup="listbox"
        class="flex w-full items-center justify-between gap-4 rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] px-4 py-3.5 text-right transition hover:border-[var(--livora-ink)]"
    >

        <span>
            <span class="block text-[10px] uppercase tracking-[0.16em] text-[var(--livora-stone)]">
                SORT
            </span>

            <span
                x-text="label"
                class="mt-1 block text-xs font-medium text-[var(--livora-text)]"
            >
                {{ $currentLabel }}
            </span>
        </span>

        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            class="h-4 w-4 shrink-0 text-[var(--livora-stone)] transition-transform"
            :class="open ? 'rotate-180' : ''"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="m19.5 8.25-7.5 7.5-7.5-7.5"
            />
        </svg>

    </button>


    {{-- =========================================================
         Dropdown
    ========================================================== --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="translate-y-1 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-1 opacity-0"
        class="absolute left-0 right-0 top-[calc(100%+8px)] z-30 overflow-hidden rounded-2xl border border-[var(--livora-border)] bg-[var(--livora-white)] p-2 shadow-xl"
    >

        <div
            role="listbox"
            aria-label="مرتب‌سازی محصولات"
            class="space-y-1"
        >

            @foreach($sortOptions as $value => $label)

                @php
                    $query = array_merge(
                        $baseQuery,
                        ['sort' => $value]
                    );

                    $url =
                        route('shop.index')
                        . '?'
                        . http_build_query($query);
                @endphp

                <a
                    href="{{ $url }}"
                    role="option"
                    aria-selected="{{ $currentSort === $value ? 'true' : 'false' }}"
                    @click="
                        value = @js($value);
                        label = @js($label);
                        open = false;
                    "
                    @class([
                        'flex items-center justify-between rounded-xl px-3 py-3 text-xs transition',
                        'bg-[var(--livora-surface)] font-semibold text-[var(--livora-text)]'
                            => $currentSort === $value,
                        'text-[var(--livora-stone)] hover:bg-[var(--livora-surface)] hover:text-[var(--livora-text)]'
                            => $currentSort !== $value,
                    ])
                >

                    <span>
                        {{ $label }}
                    </span>

                    @if($currentSort === $value)

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="h-4 w-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m4.5 12.75 6 6 9-13.5"
                            />
                        </svg>

                    @endif

                </a>

            @endforeach

        </div>

    </div>

</div>
