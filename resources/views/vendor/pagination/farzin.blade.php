@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        $from = $paginator->firstItem();
        $to = $paginator->lastItem();
        $total = $paginator->total();
        $window = 2;
        $pages = [];

        if ($lastPage <= 7) {
            for ($page = 1; $page <= $lastPage; $page++) {
                $pages[] = $page;
            }
        } else {
            $pages[] = 1;
            $start = max(2, $currentPage - $window);
            $end = min($lastPage - 1, $currentPage + $window);

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

    <nav class="farzin-paginator" aria-label="صفحه‌بندی" dir="rtl">
        <div class="farzin-paginator__layout">
            @if ($from !== null && $to !== null)
                <p class="farzin-paginator__summary">
                    نمایش
                    <strong>{{ \App\Helpers\PersianNumberHelper::format($from) }}</strong>
                    تا
                    <strong>{{ \App\Helpers\PersianNumberHelper::format($to) }}</strong>
                    از
                    <strong>{{ \App\Helpers\PersianNumberHelper::format($total) }}</strong>
                    مورد
                </p>
            @endif

            <div class="farzin-paginator__controls">
                @if ($paginator->onFirstPage())
                    <span class="farzin-paginator__item farzin-paginator__step farzin-paginator__step--disabled"
                          aria-disabled="true" aria-label="صفحه قبل">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                        </svg>
                    </span>
                @else
                    <a class="farzin-paginator__item farzin-paginator__step"
                       href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="صفحه قبل">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                        </svg>
                    </a>
                @endif

                <div class="farzin-paginator__pages" aria-label="شماره صفحه‌ها">
                    @foreach ($pages as $page)
                        @if ($page === '...')
                            <span class="farzin-paginator__item farzin-paginator__ellipsis" aria-hidden="true">…</span>
                        @elseif ($page === $currentPage)
                            <span class="farzin-paginator__item farzin-paginator__page farzin-paginator__page--current"
                                  aria-current="page" aria-label="صفحه {{ \App\Helpers\PersianNumberHelper::format($page) }}">
                                {{ \App\Helpers\PersianNumberHelper::format($page) }}
                            </span>
                        @else
                            <a class="farzin-paginator__item farzin-paginator__page {{ abs($page - $currentPage) > 1 ? 'farzin-paginator__page--mobile-hide' : '' }}"
                               href="{{ $paginator->url($page) }}"
                               aria-label="صفحه {{ \App\Helpers\PersianNumberHelper::format($page) }}">
                                {{ \App\Helpers\PersianNumberHelper::format($page) }}
                            </a>
                        @endif
                    @endforeach
                </div>

                @if ($paginator->hasMorePages())
                    <a class="farzin-paginator__item farzin-paginator__step"
                       href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="صفحه بعد">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                        </svg>
                    </a>
                @else
                    <span class="farzin-paginator__item farzin-paginator__step farzin-paginator__step--disabled"
                          aria-disabled="true" aria-label="صفحه بعد">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
