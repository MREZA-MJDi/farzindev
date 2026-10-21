@props([
'paginator' => null,
])

@php
    /*
    |--------------------------------------------------------------------------
    | Resolve paginator
    |--------------------------------------------------------------------------
    |
    | Supports:
    | - Explicit paginator prop
    | - $paginator variable
    | - Laravel paginator instances passed directly
    |
    */

    $pagination = $paginator;

    if (! $pagination) {
        return;
    }

    $currentPage = $pagination->currentPage();
    $lastPage = $pagination->lastPage();
    $total = $pagination->total();
    $perPage = $pagination->perPage();

    $from = $pagination->firstItem();
    $to = $pagination->lastItem();

    $window = 2;

    $pages = [];

    /*
     |--------------------------------------------------------------------------
     | Build compact page list
     |--------------------------------------------------------------------------
     */

    if ($lastPage <= 7) {

        for ($page = 1; $page <= $lastPage; $page++) {
            $pages[] = $page;
        }

    } else {

        $pages[] = 1;

        $start = max(
            2,
            $currentPage - $window
        );

        $end = min(
            $lastPage - 1,
            $currentPage + $window
        );

        if ($start > 2) {
            $pages[] = '...';
        }

        for ($page = $start; $page <= $end; $page++) {
            $pages[] = $page;
        }

        if ($end < $lastPage - 1) {
            $pages[] = '...';
        }

        $pages[] = $lastPage;
    }
@endphp

@if($pagination->hasPages())

    <nav
        aria-label="صفحه‌بندی محصولات"
        class="farzin-pagination mt-8 sm:mt-10"
        dir="rtl"
    >

        <div class="farzin-pagination__layout flex min-w-0 flex-col gap-4 md:flex-row md:items-center md:justify-between md:gap-6">

            {{-- =====================================================
                 Result Summary
            ====================================================== --}}
            @if($from && $to)

                <p class="farzin-pagination__summary text-[11px] text-[var(--livora-stone)]">

                    نمایش
                    <span class="font-medium text-[var(--livora-ink)]">
                        {{ number_format($from) }}
                    </span>

                    تا

                    <span class="font-medium text-[var(--livora-ink)]">
                        {{ number_format($to) }}
                    </span>

                    از

                    <span class="font-medium text-[var(--livora-ink)]">
                        {{ number_format($total) }}
                    </span>

                    محصول

                </p>

            @endif


            {{-- =====================================================
                 Pagination
            ====================================================== --}}
            <div class="farzin-pagination__controls flex w-full min-w-0 flex-wrap items-center justify-center gap-1.5 md:w-auto md:justify-start md:gap-2">

                {{-- Previous --}}
                @if($pagination->onFirstPage())

                    <span
                        aria-disabled="true"
                        aria-label="صفحه قبلی"
                        class="farzin-pagination__step flex h-10 w-10 shrink-0 cursor-not-allowed items-center justify-center rounded-xl border border-[var(--livora-border)] bg-[var(--livora-surface)] text-[var(--livora-stone)] opacity-40"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-4 w-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m15.75 19.5-7.5-7.5 7.5-7.5"
                            />
                        </svg>

                    </span>

                @else

                    <a
                        href="{{ $pagination->previousPageUrl() }}"
                        rel="prev"
                        aria-label="صفحه قبلی"
                        class="farzin-pagination__step flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] text-[var(--livora-ink)] transition hover:border-[var(--livora-ink)] hover:bg-[var(--livora-surface)]"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-4 w-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m15.75 19.5-7.5-7.5 7.5-7.5"
                            />
                        </svg>

                    </a>

                @endif


                {{-- Pages --}}
                <div class="farzin-pagination__pages flex min-w-0 items-center gap-1">

                    @foreach($pages as $page)

                        @if($page === '...')

                            <span
                                aria-hidden="true"
                                class="farzin-pagination__ellipsis hidden h-10 min-w-10 items-center justify-center rounded-xl px-2 text-xs text-[var(--livora-stone)] md:flex"
                            >
                                …
                            </span>

                        @elseif($page === $currentPage)

                            <span
                                aria-current="page"
                                aria-label="صفحه {{ $page }}"
                                class="farzin-pagination__page flex h-10 min-w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--livora-ink)] px-3 text-xs font-semibold text-white"
                            >
                                {{ number_format($page) }}
                            </span>

                        @else

                            <a
                                href="{{ $pagination->url($page) }}"
                                aria-label="صفحه {{ $page }}"
                                @class([
                                    'farzin-pagination__page flex h-10 min-w-10 shrink-0 items-center justify-center rounded-xl border border-transparent px-3 text-xs font-medium text-[var(--livora-stone)] transition hover:border-[var(--livora-border)] hover:bg-[var(--livora-white)] hover:text-[var(--livora-ink)]',
                                    'farzin-pagination__page--mobile-hide' => $page !== 1 && $page !== $lastPage && abs($page - $currentPage) > 1,
                                ])
                            >
                                {{ number_format($page) }}
                            </a>

                        @endif

                    @endforeach

                </div>


                {{-- Next --}}
                @if($pagination->hasMorePages())

                    <a
                        href="{{ $pagination->nextPageUrl() }}"
                        rel="next"
                        aria-label="صفحه بعدی"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--livora-border)] bg-[var(--livora-white)] text-[var(--livora-ink)] transition hover:border-[var(--livora-ink)] hover:bg-[var(--livora-surface)]"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-4 w-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m8.25 4.5 7.5 7.5 7.5 7.5"
                            />
                        </svg>

                    </a>

                @else

                    <span
                        aria-disabled="true"
                        aria-label="صفحه بعدی"
                        class="flex h-10 w-10 cursor-not-allowed items-center justify-center rounded-xl border border-[var(--livora-border)] bg-[var(--livora-surface)] text-[var(--livora-stone)] opacity-40"
                    >

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-4 w-4"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m8.25 4.5 7.5 7.5 7.5 7.5"
                            />
                        </svg>

                    </span>

                @endif

            </div>

        </div>

    </nav>

@endif
