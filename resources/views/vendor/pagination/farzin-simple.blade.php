@if ($paginator->hasPages())
    <nav class="farzin-paginator" aria-label="صفحه‌بندی" dir="rtl">
        <div class="farzin-paginator__layout">
            <p class="farzin-paginator__summary">
                صفحه {{ \App\Helpers\PersianNumberHelper::format($paginator->currentPage()) }}
            </p>

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
