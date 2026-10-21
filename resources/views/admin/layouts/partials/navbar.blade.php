@php
    $adminUser = auth()->user();

    $currentRouteName = request()->route()?->getName();

    $pageTitles = [
        'admin.dashboard' => 'داشبورد',
        'admin.storefront-settings.edit' => 'تنظیمات فروشگاه',

        'admin.products.index' => 'محصولات',
        'admin.products.create' => 'افزودن محصول',
        'admin.products.edit' => 'ویرایش محصول',
        'admin.products.show' => 'جزئیات محصول',

        'admin.categories.index' => 'دسته‌بندی‌ها',
        'admin.categories.create' => 'افزودن دسته‌بندی',
        'admin.categories.edit' => 'ویرایش دسته‌بندی',
        'admin.categories.show' => 'جزئیات دسته‌بندی',

        'admin.orders.index' => 'سفارش‌ها',
        'admin.orders.show' => 'جزئیات سفارش',

        'admin.customers.index' => 'مشتریان',
        'admin.customers.show' => 'جزئیات مشتری',
        'admin.customers.edit' => 'ویرایش مشتری',

        'admin.product-images.index' => 'تصاویر محصولات',
        'admin.product-images.create' => 'افزودن تصویر محصول',
        'admin.product-images.edit' => 'ویرایش تصویر محصول',
        'admin.product-images.show' => 'جزئیات تصویر محصول',

        'admin.product-variants.index' => 'تنوع‌های محصول',
        'admin.product-variants.create' => 'افزودن تنوع محصول',
        'admin.product-variants.edit' => 'ویرایش تنوع محصول',
        'admin.product-variants.show' => 'جزئیات تنوع محصول',

        'admin.media.index' => 'رسانه',
        'admin.media.create' => 'افزودن رسانه',

        'admin.contact-messages.index' => 'پیام‌های کاربران',
        'admin.contact-messages.show' => 'مشاهده پیام',
    ];

    $pageTitle = $pageTitles[$currentRouteName]
        ?? 'مدیریت فروشگاه';

    $quickSearchPages = [
        ['title' => 'داشبورد', 'url' => route('admin.dashboard'), 'terms' => 'home overview dashboard'],
        ['title' => 'تنظیمات فروشگاه', 'url' => route('admin.storefront-settings.edit'), 'terms' => 'banner hero contact settings'],
        ['title' => 'محصولات', 'url' => route('admin.products.index'), 'terms' => 'product catalog کالا'],
        ['title' => 'دسته‌بندی‌ها', 'url' => route('admin.categories.index'), 'terms' => 'category دسته ها'],
        ['title' => 'تصاویر محصولات', 'url' => route('admin.product-images.index'), 'terms' => 'images photos media تصویر'],
        ['title' => 'تنوع‌های محصول', 'url' => route('admin.product-variants.index'), 'terms' => 'variants options ویژگی'],
        ['title' => 'سفارش‌ها', 'url' => route('admin.orders.index'), 'terms' => 'orders sales'],
        ['title' => 'مشتریان', 'url' => route('admin.customers.index'), 'terms' => 'customers users'],
        ['title' => 'رسانه', 'url' => route('admin.media.index'), 'terms' => 'media uploads فایل آپلود'],
        ['title' => 'پیام‌های کاربران', 'url' => route('admin.contact-messages.index'), 'terms' => 'contact messages support'],
    ];


    /*
    |--------------------------------------------------------------------------
    | Admin Notifications
    |--------------------------------------------------------------------------
    */

    $pendingOrdersCount = \App\Models\Order::query()
        ->whereIn('status', [
            'pending',
            'processing',
        ])
        ->count();

    $outOfStockCount = \App\Models\Product::query()
        ->where('stock', 0)
        ->count();

    $unreadContactMessagesCount = \App\Models\ContactMessage::query()
        ->where('status', 'unread')
        ->count();

    /*
     * هر نوع اعلان یک notification محسوب می‌شود.
     *
     * بنابراین:
     *
     * 5 پیام جدید      => 5
     * 3 سفارش           => 3
     * محصولات بدون موجودی => 1 notification
     */
    $totalNotificationCount =
        $unreadContactMessagesCount
        + $pendingOrdersCount
        + ($outOfStockCount > 0 ? 1 : 0);
@endphp


<header
    class="admin-navbar sticky top-0 z-30 flex min-w-0 h-[72px] shrink-0 items-center border-b border-[var(--admin-border)] bg-[var(--admin-white)]/95 backdrop-blur-xl"
>

    <div class="admin-navbar__inner flex min-w-0 w-full items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">


        {{-- =========================================================
             LEFT / PAGE INFO
        ========================================================== --}}

        <div class="flex min-w-0 items-center gap-3">


            {{-- Mobile Sidebar Toggle --}}

            <button
                type="button"
                @click="sidebarOpen = true"
                aria-label="باز کردن منوی مدیریت"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-[var(--admin-border)] bg-[var(--admin-white)] text-[var(--admin-text-soft)] transition hover:border-[var(--admin-border-dark)] hover:bg-[var(--admin-surface)] lg:hidden"
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
                        d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"
                    />
                </svg>

            </button>


            <div class="min-w-0">

                {{-- Breadcrumb-ish label --}}

                <div class="hidden items-center gap-2 text-[9px] text-[var(--admin-muted)] sm:flex">

                    <span>
                        فرزین
                    </span>

                    <span>
                        /
                    </span>

                    <span>
                        ADMIN
                    </span>

                </div>


                <h1 class="mt-0.5 truncate text-sm font-bold text-[var(--admin-text)] sm:text-base">
                    {{ $pageTitle }}
                </h1>

            </div>

        </div>


        {{-- =========================================================
             RIGHT ACTIONS
        ========================================================== --}}

        <div class="admin-navbar__actions flex shrink-0 items-center gap-2 sm:gap-3">


            {{-- =====================================================
                 VISIT STORE
            ====================================================== --}}

            <a
                href="{{ route('home') }}"
                target="_blank"
                rel="noopener"
                class="hidden items-center gap-2 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-white)] px-3 py-2 text-[10px] font-medium text-[var(--admin-text-soft)] transition hover:border-[var(--admin-border-dark)] hover:bg-[var(--admin-surface)] sm:flex"
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

                فروشگاه

                <span class="text-[9px] text-[var(--admin-muted)]">
                    ↗
                </span>

            </a>


            {{-- =====================================================
                 SEARCH
            ====================================================== --}}

            <div
                class="relative hidden md:block"
                x-data="adminQuickSearch"
                data-pages='@json($quickSearchPages)'
                @click.outside="query = ''"
            >
                <form role="search" @submit.prevent="goToFirst()">
                    <label for="admin-quick-search" class="sr-only">جست‌وجوی بخش‌های پنل</label>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--admin-muted)]"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m21 21-4.35-4.35m1.35-5.4a6.75 6.75 0 1 1-13.5 0 6.75 6.75 0 0 1 13.5 0Z"
                        />
                    </svg>

                    <input
                        id="admin-quick-search"
                        type="search"
                        x-model="query"
                        @keydown.escape="query = ''"
                        :aria-expanded="(query.trim().length > 0).toString()"
                        aria-controls="admin-quick-search-results"
                        autocomplete="off"
                        placeholder="جست‌وجوی بخش‌های پنل..."
                        class="h-10 w-44 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface)] py-2 pr-10 pl-3 text-[10px] text-[var(--admin-text)] outline-none transition placeholder:text-[var(--admin-muted)] focus:border-[var(--admin-accent)] focus:bg-[var(--admin-white)] xl:w-56"
                    >
                </form>

                <div
                    id="admin-quick-search-results"
                    x-show="query.trim().length > 0"
                    x-cloak
                    x-transition
                    class="absolute left-0 top-[calc(100%+8px)] z-50 w-64 overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-2 shadow-[var(--admin-shadow-lg)]"
                >
                    <template x-for="page in matches" :key="page.url">
                        <a
                            :href="page.url"
                            @click="query = ''"
                            class="block rounded-xl px-3 py-2.5 text-xs text-[var(--admin-text)] transition hover:bg-[var(--admin-surface)]"
                        >
                            <span x-text="page.title"></span>
                            <span class="mr-2 text-[10px] text-[var(--admin-muted)]" aria-hidden="true">↗</span>
                        </a>
                    </template>
                    <p x-show="matches.length === 0" class="px-3 py-3 text-xs text-[var(--admin-muted)]">
                        بخشی پیدا نشد؛ نام صفحه را ساده‌تر وارد کنید.
                    </p>
                </div>
            </div>


            {{-- =====================================================
                 NOTIFICATIONS
            ====================================================== --}}

            <div
                class="relative"
                @click.outside="notificationOpen = false"
            >

                <button
                    type="button"
                    @click="notificationOpen = !notificationOpen"
                    :aria-expanded="notificationOpen.toString()"
                    aria-label="اعلان‌ها"
                    class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--admin-border)] bg-[var(--admin-white)] text-[var(--admin-text-soft)] transition hover:border-[var(--admin-border-dark)] hover:bg-[var(--admin-surface)]"
                >

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="h-[18px] w-[18px]"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9a6 6 0 1 0-12 0v.75a8.967 8.967 0 0 1-2.313 6.022c1.733.64 3.599 1.078 5.454 1.31m5.716 0a24.255 24.255 0 0 1-5.716 0m5.716 0a3 3 0 1 1-5.716 0"
                        />
                    </svg>


                    @if($totalNotificationCount > 0)

                        <span
                            class="absolute -right-1 -top-1 flex min-h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[9px] font-bold leading-none text-white ring-2 ring-[var(--admin-white)]"
                        >
                            {{ $totalNotificationCount > 99 ? '99+' : $totalNotificationCount }}
                        </span>

                    @endif

                </button>


                {{-- Notification Dropdown --}}

                <div
                    x-show="notificationOpen"
                    x-cloak
                    x-transition
                    class="absolute left-0 top-[calc(100%+10px)] z-50 w-80 overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] shadow-[var(--admin-shadow-lg)]"
                >


                    {{-- Header --}}

                    <div class="flex items-center justify-between border-b border-[var(--admin-border)] px-4 py-4">

                        <div>

                            <p class="text-xs font-bold text-[var(--admin-text)]">
                                اعلان‌ها
                            </p>

                            <p class="mt-1 text-[9px] text-[var(--admin-muted)]">
                                وضعیت‌های مهم فروشگاه
                            </p>

                        </div>


                        @if($totalNotificationCount > 0)

                            <span class="rounded-full bg-red-50 px-2 py-1 text-[9px] font-semibold text-red-600">
                                {{ number_format($totalNotificationCount) }}
                                مورد
                            </span>

                        @else

                            <span class="rounded-full bg-[var(--admin-accent-soft)] px-2 py-1 text-[9px] font-semibold text-[var(--admin-accent-dark)]">
                                LIVE
                            </span>

                        @endif

                    </div>


                    {{-- Notification Items --}}

                    <div class="p-3">


                        {{-- =================================================
                             CONTACT MESSAGES
                        ================================================== --}}

                        @if($unreadContactMessagesCount > 0)

                            <a
                                href="{{ route('admin.contact-messages.index') }}"
                                @click="notificationOpen = false"
                                class="flex gap-3 rounded-xl p-3 transition hover:bg-[var(--admin-surface)]"
                            >

                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
                                    @
                                </span>

                                <div class="min-w-0">

                                    <p class="text-[10px] font-semibold text-[var(--admin-text)]">
                                        پیام جدید از کاربران
                                    </p>

                                    <p class="mt-1 text-[9px] leading-5 text-[var(--admin-muted)]">
                                        {{ number_format($unreadContactMessagesCount) }}
                                        پیام جدید برای بررسی دارید.
                                    </p>

                                </div>

                            </a>

                        @endif


                        {{-- =================================================
                             ORDERS
                        ================================================== --}}

                        @if($pendingOrdersCount > 0)

                            <a
                                href="{{ route('admin.orders.index') }}"
                                @click="notificationOpen = false"
                                class="mt-1 flex gap-3 rounded-xl p-3 transition hover:bg-[var(--admin-surface)]"
                            >

                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                                    !
                                </span>

                                <div class="min-w-0">

                                    <p class="text-[10px] font-semibold text-[var(--admin-text)]">
                                        سفارش‌ها را بررسی کن
                                    </p>

                                    <p class="mt-1 text-[9px] leading-5 text-[var(--admin-muted)]">
                                        {{ number_format($pendingOrdersCount) }}
                                        سفارش نیازمند بررسی است.
                                    </p>

                                </div>

                            </a>

                        @endif


                        {{-- =================================================
                             OUT OF STOCK
                        ================================================== --}}

                        @if($outOfStockCount > 0)

                            <a
                                href="{{ route('admin.products.index') }}"
                                @click="notificationOpen = false"
                                class="mt-1 flex gap-3 rounded-xl p-3 transition hover:bg-[var(--admin-surface)]"
                            >

                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-700">
                                    !
                                </span>

                                <div class="min-w-0">

                                    <p class="text-[10px] font-semibold text-[var(--admin-text)]">
                                        موجودی محصولات
                                    </p>

                                    <p class="mt-1 text-[9px] leading-5 text-[var(--admin-muted)]">
                                        {{ number_format($outOfStockCount) }}
                                        محصول بدون موجودی است.
                                    </p>

                                </div>

                            </a>

                        @endif


                        {{-- =================================================
                             EMPTY STATE
                        ================================================== --}}

                        @if(
                            $unreadContactMessagesCount === 0
                            && $pendingOrdersCount === 0
                            && $outOfStockCount === 0
                        )

                            <div class="px-3 py-8 text-center">

                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--admin-surface)] text-[var(--admin-muted)]">
                                    ✓
                                </div>

                                <p class="mt-3 text-[10px] font-semibold text-[var(--admin-text)]">
                                    همه‌چیز مرتب است
                                </p>

                                <p class="mt-1 text-[9px] leading-5 text-[var(--admin-muted)]">
                                    در حال حاضر اعلان مهمی ندارید.
                                </p>

                            </div>

                        @endif

                    </div>

                </div>

            </div>


            {{-- =====================================================
                 DIVIDER
            ====================================================== --}}

            <span class="hidden h-7 w-px bg-[var(--admin-border)] sm:block"></span>


            {{-- =====================================================
                 USER MENU
            ====================================================== --}}

            <div
                class="relative"
                @click.outside="userMenuOpen = false"
            >

                <button
                    type="button"
                    @click="userMenuOpen = !userMenuOpen"
                    :aria-expanded="userMenuOpen.toString()"
                    class="flex items-center gap-2 rounded-xl p-1.5 transition hover:bg-[var(--admin-surface)]"
                >

                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--admin-text)] text-xs font-bold text-white">
                        {{ mb_substr($adminUser?->name ?: 'A', 0, 1) }}
                    </span>

                    <span class="hidden text-right sm:block">

                        <span class="block max-w-28 truncate text-[10px] font-semibold text-[var(--admin-text)]">
                            {{ $adminUser?->name ?: 'مدیر فروشگاه' }}
                        </span>

                        <span class="mt-0.5 block text-[8px] text-[var(--admin-muted)]">
                            Administrator
                        </span>

                    </span>


                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="hidden h-3.5 w-3.5 text-[var(--admin-muted)] sm:block"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m19.5 8.25-7.5 7.5-7.5-7.5"
                        />
                    </svg>

                </button>


                {{-- User Dropdown --}}

                <div
                    x-show="userMenuOpen"
                    x-cloak
                    x-transition
                    class="absolute left-0 top-[calc(100%+10px)] z-50 w-60 overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-white)] shadow-[var(--admin-shadow-lg)]"
                >

                    <div class="border-b border-[var(--admin-border)] bg-[var(--admin-surface)] p-4">

                        <p class="truncate text-xs font-bold text-[var(--admin-text)]">
                            {{ $adminUser?->name ?: 'مدیر فروشگاه' }}
                        </p>

                        @if($adminUser?->email)

                            <p class="mt-1 truncate text-[9px] text-[var(--admin-muted)]">
                                {{ $adminUser->email }}
                            </p>

                        @endif

                    </div>


                    <div class="p-2">

                        <a
                            href="{{ route('admin.dashboard') }}"
                            @click="userMenuOpen = false"
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[10px] text-[var(--admin-text-soft)] transition hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]"
                        >

                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--admin-surface)]">
                                ◎
                            </span>

                            داشبورد

                        </a>


                        <a
                            href="{{ route('home') }}"
                            target="_blank"
                            rel="noopener"
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[10px] text-[var(--admin-text-soft)] transition hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]"
                        >

                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-[var(--admin-surface)]">
                                ↗
                            </span>

                            مشاهده فروشگاه

                        </a>

                    </div>


                    <div class="border-t border-[var(--admin-border)] p-2">

                        <form
                            action="{{ route('logout') }}"
                            method="POST"
                        >

                            @csrf

                            <button
                                type="submit"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-right text-[10px] text-[var(--admin-danger)] transition hover:bg-[var(--admin-danger-bg)]"
                            >

                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50">
                                    ↪
                                </span>

                                خروج از حساب

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</header>
