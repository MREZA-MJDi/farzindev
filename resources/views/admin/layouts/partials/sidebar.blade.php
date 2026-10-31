<aside
    class="fixed inset-y-0 right-0 z-50 flex w-[260px] -translate-x-0 flex-col border-l border-[var(--admin-border)] bg-[var(--admin-white)] transition-transform duration-300 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
>

    {{-- =========================================================
         BRAND
    ========================================================== --}}
    <div class="flex h-[72px] shrink-0 items-center justify-between border-b border-[var(--admin-border)] px-5">

        <a
            href="{{ route('admin.dashboard') }}"
            class="group flex min-w-0 items-center gap-3"
        >

            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[var(--admin-text)] text-xs font-bold tracking-[0.12em] text-white transition duration-300 group-hover:bg-[var(--admin-accent)]"
            >
                FZ
            </div>

            <div class="min-w-0">

                <p class="truncate text-sm font-bold tracking-[0.18em] text-[var(--admin-text)]">
                    فرزین
                </p>

                <p class="mt-0.5 text-[9px] uppercase tracking-[0.16em] text-[var(--admin-muted)]">
                    Administration
                </p>

            </div>

        </a>


        {{-- Mobile close --}}
        <button
            type="button"
            @click="sidebarOpen = false"
            aria-label="بستن منوی مدیریت"
            class="flex h-9 w-9 items-center justify-center rounded-xl text-[var(--admin-muted)] transition hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)] lg:hidden"
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
                    d="M6 18 18 6M6 6l12 12"
                />
            </svg>

        </button>

    </div>


    {{-- =========================================================
         NAVIGATION
    ========================================================== --}}
    <nav
        class="flex-1 overflow-y-auto px-3 py-5"
        aria-label="منوی مدیریت"
    >

        {{-- Dashboard --}}
        <div class="mb-5">

            <p class="px-3 text-[9px] font-bold uppercase tracking-[0.2em] text-[var(--admin-muted)]">
                OVERVIEW
            </p>

            <div class="mt-2 space-y-1">

                <a
                    href="{{ route('admin.dashboard') }}"
                    @class([
                        'group flex items-center gap-3 rounded-2xl px-3 py-3 text-xs font-medium transition',
                        'bg-[var(--admin-text)] text-white shadow-sm' => request()->routeIs('admin.dashboard'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.dashboard'),
                    ])
                >

                    <span
                        @class([
                            'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl transition',
                            'bg-white/10 text-white' => request()->routeIs('admin.dashboard'),
                            'bg-[var(--admin-surface)] text-[var(--admin-muted)] group-hover:text-[var(--admin-text)]' => !request()->routeIs('admin.dashboard'),
                        ])
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
                                d="M3.75 3.75h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Zm-10 10h6.5v6.5h-6.5v-6.5Zm10 0h6.5v6.5h-6.5v-6.5Z"
                            />
                        </svg>

                    </span>

                    <span class="flex-1">
                        داشبورد
                    </span>

                    @if(request()->routeIs('admin.dashboard'))

                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--admin-accent)]"></span>

                    @endif

                </a>

                <a
                    href="{{ route('admin.blog-terms.index') }}"
                    @class([
                        'group flex items-center gap-3 rounded-2xl px-3 py-3 text-xs font-medium transition',
                        'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.blog-terms.*'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.blog-terms.*'),
                    ])
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-[var(--admin-muted)]" aria-hidden="true">TAG</span>
                    <span class="flex-1">دسته‌ها و برچسب‌های بلاگ</span>
                    @if(request()->routeIs('admin.blog-terms.*'))
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--admin-accent)]"></span>
                    @endif
                </a>

                <a
                    href="{{ route('admin.blog-appearance.edit') }}"
                    @class([
                        'group flex items-center gap-3 rounded-2xl px-3 py-3 text-xs font-medium transition',
                        'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.blog-appearance.*'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.blog-appearance.*'),
                    ])
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-[var(--admin-muted)]" aria-hidden="true">ART</span>
                    <span class="flex-1">ظاهر و تصاویر مجله</span>
                    @if(request()->routeIs('admin.blog-appearance.*'))
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--admin-accent)]"></span>
                    @endif
                </a>

            </div>

        </div>


        {{-- =====================================================
             CATALOG
        ====================================================== --}}
        <div class="mb-5">

            <p class="px-3 text-[9px] font-bold uppercase tracking-[0.2em] text-[var(--admin-muted)]">
                CATALOG
            </p>

            <div class="mt-2 space-y-1">

                {{-- Products --}}
                <a
                    href="{{ route('admin.products.index') }}"
                    @class([
                        'group flex items-center gap-3 rounded-2xl px-3 py-3 text-xs font-medium transition',
                        'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.products.*'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.products.*'),
                    ])
                >

                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-[var(--admin-muted)] group-hover:text-[var(--admin-text)]"
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
                                d="m20.25 7.5-8.25-4.5-8.25 4.5m16.5 0-8.25 4.5m8.25-4.5V16.5L12 21l-8.25-4.5V7.5m0 0L12 12m0 0v9"
                            />
                        </svg>

                    </span>

                    <span class="flex-1">
                        محصولات
                    </span>

                    @if(request()->routeIs('admin.products.*'))

                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--admin-accent)]"></span>

                    @endif

                </a>


                {{-- Categories --}}
                <a
                    href="{{ route('admin.categories.index') }}"
                    @class([
                        'group flex items-center gap-3 rounded-2xl px-3 py-3 text-xs font-medium transition',
                        'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.categories.*'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.categories.*'),
                    ])
                >

                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-[var(--admin-muted)]"
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
                                d="M3.75 6.75h6.5v4.5h-6.5v-4.5Zm10 0h6.5v4.5h-6.5v-4.5Zm-10 6h6.5v4.5h-6.5v-4.5Zm10 0h6.5v4.5h-6.5v-4.5Z"
                            />
                        </svg>

                    </span>

                    <span class="flex-1">
                        دسته‌بندی‌ها
                    </span>

                    @if(request()->routeIs('admin.categories.*'))

                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--admin-accent)]"></span>

                    @endif

                </a>


                {{-- Media --}}
                <a
                    href="{{ route('admin.media.index') }}"
                    @class([
                        'group flex items-center gap-3 rounded-2xl px-3 py-3 text-xs font-medium transition',
                        'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.media.*'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.media.*'),
                    ])
                >

                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-[var(--admin-muted)]"
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
                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.159 2.159m0 0 1.409-1.409a2.25 2.25 0 0 1 3.182 0l4.909 4.909M21.75 12V6.75a2.25 2.25 0 0 0-2.25-2.25H4.5a2.25 2.25 0 0 0-2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25h10.5"
                            />
                        </svg>

                    </span>

                    <span class="flex-1">
                        رسانه
                    </span>

                    @if(request()->routeIs('admin.media.*'))

                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--admin-accent)]"></span>

                    @endif

                </a>





                <a
                    href="{{ route('admin.blog-posts.index') }}"
                    @class([
                        'group flex items-center gap-3 rounded-2xl px-3 py-3 text-xs font-medium transition',
                        'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.blog-posts.*'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.blog-posts.*'),
                    ])
                >
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-[var(--admin-muted)]" aria-hidden="true">BLOG</span>
                    <span class="flex-1">مقالات و بلاگ</span>
                    @if(request()->routeIs('admin.blog-posts.*'))
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--admin-accent)]"></span>
                    @endif
                </a>

            </div>

        </div>


        {{-- =====================================================
             SALES
        ====================================================== --}}
        <div class="mb-5">

            <p class="px-3 text-[9px] font-bold uppercase tracking-[0.2em] text-[var(--admin-muted)]">
                SALES
            </p>

            <div class="mt-2 space-y-1">

                {{-- Orders --}}
                <a
                    href="{{ route('admin.orders.index') }}"
                    @class([
                        'group flex items-center gap-3 rounded-2xl px-3 py-3 text-xs font-medium transition',
                        'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.orders.*'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.orders.*'),
                    ])
                >

                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-[var(--admin-muted)]"
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
                                d="M6.75 6.75h10.5M6.75 10.5h7.5m-7.5 3.75h7.5M4.5 3.75h15A1.5 1.5 0 0 1 21 5.25v13.5a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.75V5.25a1.5 1.5 0 0 1 1.5-1.5Z"
                            />
                        </svg>

                    </span>

                    <span class="flex-1">
                        سفارش‌ها
                    </span>

                    @if(request()->routeIs('admin.orders.*'))

                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--admin-accent)]"></span>

                    @endif

                </a>


                {{-- Customers --}}
                <a
                    href="{{ route('admin.customers.index') }}"
                    @class([
                        'group flex items-center gap-3 rounded-2xl px-3 py-3 text-xs font-medium transition',
                        'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.customers.*'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.customers.*'),
                    ])
                >

                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-[var(--admin-muted)]"
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
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0ZM19.5 20.25a4.5 4.5 0 0 0-3.75-4.44"
                            />
                        </svg>

                    </span>

                    <span class="flex-1">
                        مشتریان
                    </span>

                    @if(request()->routeIs('admin.customers.*'))

                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--admin-accent)]"></span>

                    @endif

                </a>


                {{-- Storefront Settings --}}
                <a
                    href="{{ route('admin.storefront-settings.edit') }}"
                    @class([
                        'group flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-medium transition duration-200',
                        'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.storefront-settings.*'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.storefront-settings.*'),
                    ])
                >
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/70 text-sm" aria-hidden="true">⚙</span>
                    <span class="min-w-0 flex-1 truncate">تنظیمات فروشگاه</span>
                </a>

                {{-- SEO / Search visibility --}}
                <a
                    href="{{ route('admin.seo-settings.edit') }}"
                    @class([
                        'group flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-medium transition duration-200',
                        'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.seo-settings.*'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.seo-settings.*'),
                    ])
                >
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/70 text-sm" aria-hidden="true">⌕</span>
                    <span class="min-w-0 flex-1 truncate">سئو و ایندکس</span>
                </a>

                <a
                    href="{{ route('admin.seo-redirects.index') }}"
                    @class([
                        'group flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-medium transition duration-200',
                        'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.seo-redirects.*'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.seo-redirects.*'),
                    ])
                >
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/70 text-sm" aria-hidden="true">↪</span>
                    <span class="min-w-0 flex-1 truncate">مدیریت Redirectها</span>
                </a>

                {{-- Contact Messages --}}
                <a
                    href="{{ route('admin.contact-messages.index') }}"
                    @class([
                        'group flex items-center gap-3 rounded-2xl px-3 py-3 text-xs font-medium transition',
                        'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => request()->routeIs('admin.contact-messages.*'),
                        'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => !request()->routeIs('admin.contact-messages.*'),
                    ])
                >

                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-[var(--admin-muted)] group-hover:text-[var(--admin-text)]"
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
                                d="M21.75 12c0 4.556-4.365 8.25-9.75 8.25a10.57 10.57 0 0 1-3.867-.72L4.5 20.25l.966-2.9A7.92 7.92 0 0 1 2.25 12c0-4.556 4.365-8.25 9.75-8.25s9.75 3.694 9.75 8.25Z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M8.25 12h.008v.008H8.25V12Zm3.742 0H12v.008h-.008V12Zm3.75 0h.008v.008h-.008V12Z"
                            />
                        </svg>

                    </span>

                    <span class="flex-1">
                        پیام‌های کاربران
                    </span>

                    @if(request()->routeIs('admin.contact-messages.*'))

                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--admin-accent)]"></span>

                    @endif

                </a>

            </div>

        </div>


        {{-- =====================================================
             STORE TOOLS
        ====================================================== --}}
        <div class="mb-5">

            <p class="px-3 text-[9px] font-bold uppercase tracking-[0.2em] text-[var(--admin-muted)]">
                STORE
            </p>

            <div class="mt-2 space-y-1">

                {{-- Frontend --}}
                <a
                    href="{{ route('home') }}"
                    target="_blank"
                    rel="noopener"
                    class="group flex items-center gap-3 rounded-2xl px-3 py-3 text-xs font-medium text-[var(--admin-text-soft)] transition hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]"
                >

                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-[var(--admin-muted)]"
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
                                d="M2.25 12s3.75-6 9.75-6 9.75 6 9.75 6-3.75 6-9.75 6-9.75-6-9.75-6Z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                            />
                        </svg>

                    </span>

                    <span class="flex-1">
                        مشاهده فروشگاه
                    </span>

                    <span class="text-[10px] text-[var(--admin-muted)]">
                        ↗
                    </span>

                </a>

            </div>

        </div>

    </nav>


    {{-- =========================================================
         BOTTOM USER PANEL
    ========================================================== --}}
    <div class="shrink-0 border-t border-[var(--admin-border)] p-3">

        @php
            $adminUser = auth()->user();
        @endphp

        <div class="rounded-2xl bg-[var(--admin-surface)] p-3">

            <div class="flex items-center gap-3">

                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-text)] text-xs font-bold text-white"
                >
                    {{ mb_substr($adminUser?->name ?: 'A', 0, 1) }}
                </div>

                <div class="min-w-0 flex-1">

                    <p class="truncate text-xs font-semibold text-[var(--admin-text)]">
                        {{ $adminUser?->name ?: 'مدیر فروشگاه' }}
                    </p>

                    <p class="mt-0.5 truncate text-[9px] text-[var(--admin-muted)]">
                        Administrator
                    </p>

                </div>

                <form
                    action="{{ route('logout') }}"
                    method="POST"
                >

                    @csrf

                    <button
                        type="submit"
                        aria-label="خروج"
                        class="flex h-8 w-8 items-center justify-center rounded-xl text-[var(--admin-muted)] transition hover:bg-white hover:text-[var(--admin-danger)]"
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
                                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-6-3 3m0 0 3 3m-3-3h8.25"
                            />
                        </svg>

                    </button>

                </form>

            </div>

        </div>

    </div>

</aside>
