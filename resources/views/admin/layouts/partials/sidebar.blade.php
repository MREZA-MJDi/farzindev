@php
    $adminNavigationGroups = [
        [
            'title' => 'نمای کلی',
            'description' => 'وضعیت کلی فروشگاه و دسترسی سریع',
            'items' => [
                ['label' => 'داشبورد اصلی', 'description' => 'خلاصه سفارش‌ها، فروش و فعالیت‌های اخیر', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => '⌂'],
            ],
        ],
        [
            'title' => 'محصولات و کاتالوگ',
            'description' => 'مدیریت کالاها، دسته‌ها و فایل‌های تصویری',
            'items' => [
                ['label' => 'محصولات', 'description' => 'افزودن و ویرایش کالا، قیمت، موجودی و مشخصات', 'route' => 'admin.products.index', 'active' => 'admin.products.*', 'icon' => '▦'],
                ['label' => 'دسته‌بندی‌ها', 'description' => 'ساخت دسته‌ها و تنظیم تصویر و معرفی هر دسته', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*', 'icon' => '▤'],
                ['label' => 'کتابخانه رسانه', 'description' => 'بارگذاری و مدیریت تصاویر و فایل‌های قابل استفاده در سایت', 'route' => 'admin.media.index', 'active' => 'admin.media.*', 'icon' => '▧'],
            ],
        ],
        [
            'title' => 'مجله و محتوای آموزشی',
            'description' => 'انتشار مقاله و مدیریت ساختار مجله',
            'items' => [
                ['label' => 'مقالات و نوشته‌ها', 'description' => 'نوشتن، ویرایش و انتشار مطالب مجله', 'route' => 'admin.blog-posts.index', 'active' => 'admin.blog-posts.*', 'icon' => '¶'],
                ['label' => 'دسته‌ها و برچسب‌ها', 'description' => 'مرتب‌سازی مطالب و تعریف موضوعات مجله', 'route' => 'admin.blog-terms.index', 'active' => 'admin.blog-terms.*', 'icon' => '◇'],
                ['label' => 'ظاهر و تصاویر مجله', 'description' => 'تنظیم تصویرها و ظاهر بخش مجله در سایت', 'route' => 'admin.blog-appearance.edit', 'active' => 'admin.blog-appearance.*', 'icon' => '▣'],
            ],
        ],
        [
            'title' => 'فروش و مشتریان',
            'description' => 'پیگیری خریدها و اطلاعات خریداران',
            'items' => [
                ['label' => 'سفارش‌ها', 'description' => 'بررسی جزئیات خرید و به‌روزرسانی وضعیت سفارش', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*', 'icon' => '▣'],
                ['label' => 'مشتریان', 'description' => 'مشاهده حساب‌ها و سوابق مشتریان', 'route' => 'admin.customers.index', 'active' => 'admin.customers.*', 'icon' => '♙'],
            ],
        ],
        [
            'title' => 'تنظیمات و حضور در گوگل',
            'description' => 'اطلاعات عمومی فروشگاه و تنظیمات جست‌وجو',
            'items' => [
                ['label' => 'تنظیمات فروشگاه', 'description' => 'بنر فروشگاه، هزینه ارسال، اطلاعات تماس و راه‌های ارتباطی', 'route' => 'admin.storefront-settings.edit', 'active' => 'admin.storefront-settings.*', 'icon' => '⚙'],
                ['label' => 'سئو و ایندکس', 'description' => 'عنوان و توضیحات نتایج جست‌وجو و تنظیمات ایندکس صفحات', 'route' => 'admin.seo-settings.edit', 'active' => 'admin.seo-settings.*', 'icon' => '⌕'],
                ['label' => 'تغییر مسیر لینک‌ها', 'description' => 'هدایت آدرس‌های قدیمی به آدرس جدید و جلوگیری از لینک شکسته', 'route' => 'admin.seo-redirects.index', 'active' => 'admin.seo-redirects.*', 'icon' => '↪'],
            ],
        ],
        [
            'title' => 'ارتباط با کاربران',
            'description' => 'دریافت و پیگیری پیام‌های ارسال‌شده از سایت',
            'items' => [
                ['label' => 'پیام‌های کاربران', 'description' => 'مشاهده پیام‌های فرم تماس و پیگیری درخواست‌ها', 'route' => 'admin.contact-messages.index', 'active' => 'admin.contact-messages.*', 'icon' => '✉'],
            ],
        ],
    ];
@endphp

<aside
    class="fixed inset-y-0 right-0 z-50 flex w-[280px] max-w-[88vw] flex-col border-l border-[var(--admin-border)] bg-[var(--admin-white)] transition-transform duration-300 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
>
    <div class="flex h-[72px] shrink-0 items-center justify-between border-b border-[var(--admin-border)] px-5">
        <a href="{{ route('admin.dashboard') }}" class="group flex min-w-0 items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[var(--admin-text)] text-xs font-bold tracking-[0.12em] text-white transition group-hover:bg-[var(--admin-accent)]">FZ</span>
            <span class="min-w-0">
                <span class="block truncate text-sm font-bold tracking-[0.08em] text-[var(--admin-text)]">فرزین</span>
                <span class="mt-0.5 block text-[10px] text-[var(--admin-muted)]">مدیریت فروشگاه</span>
            </span>
        </a>
        <button type="button" @click="sidebarOpen = false" aria-label="بستن منوی مدیریت" class="flex h-9 w-9 items-center justify-center rounded-xl text-[var(--admin-muted)] transition hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)] lg:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5" aria-label="منوی مدیریت">
        <div class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-3 py-3">
            <p class="text-xs font-bold text-[var(--admin-text)]">راهنمای منوی مدیریت</p>
            <p class="mt-1 text-[11px] leading-6 text-[var(--admin-muted)]">هر بخش برای یک کار مشخص است. توضیح زیر هر گزینه می‌گوید چه چیزی را مدیریت می‌کنید.</p>
        </div>

        @foreach ($adminNavigationGroups as $group)
            <section aria-label="{{ $group['title'] }}">
                <div class="mb-2 px-3">
                    <h2 class="text-xs font-extrabold text-[var(--admin-text)]">{{ $group['title'] }}</h2>
                    <p class="mt-1 text-[10px] leading-5 text-[var(--admin-muted)]">{{ $group['description'] }}</p>
                </div>

                <div class="space-y-1">
                    @foreach ($group['items'] as $item)
                        @php($isActive = request()->routeIs($item['active']))
                        <a
                            href="{{ route($item['route']) }}"
                            @if ($isActive) aria-current="page" @endif
                            @class([
                                'group flex items-start gap-3 rounded-xl px-3 py-3 transition duration-200',
                                'bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => $isActive,
                                'text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]' => ! $isActive,
                            ])
                        >
                            <span @class([
                                'mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-base',
                                'bg-white/80 text-[var(--admin-accent-dark)]' => $isActive,
                                'bg-[var(--admin-surface)] text-[var(--admin-muted)] group-hover:text-[var(--admin-text)]' => ! $isActive,
                            ]) aria-hidden="true">{{ $item['icon'] }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-xs font-bold">{{ $item['label'] }}</span>
                                <span @class([
                                    'mt-1 block text-[10px] leading-5',
                                    'text-[var(--admin-accent-dark)]/80' => $isActive,
                                    'text-[var(--admin-muted)]' => ! $isActive,
                                ])>{{ $item['description'] }}</span>
                            </span>
                            @if ($isActive)
                                <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-[var(--admin-accent)]" aria-hidden="true"></span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach

        <section>
            <div class="mb-2 px-3">
                <h2 class="text-xs font-extrabold text-[var(--admin-text)]">نمایش سایت</h2>
                <p class="mt-1 text-[10px] leading-5 text-[var(--admin-muted)]">برای بررسی نتیجه تغییرات، فروشگاه را در یک زبانه جدید ببینید.</p>
            </div>
            <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-xl px-3 py-3 text-[var(--admin-text-soft)] transition hover:bg-[var(--admin-surface)] hover:text-[var(--admin-text)]">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[var(--admin-surface)] text-base text-[var(--admin-muted)]" aria-hidden="true">↗</span>
                <span class="min-w-0 flex-1">
                    <span class="block text-xs font-bold">مشاهده فروشگاه</span>
                    <span class="mt-1 block text-[10px] leading-5 text-[var(--admin-muted)]">مشاهده ظاهر عمومی سایت، همان‌طور که مشتری می‌بیند.</span>
                </span>
            </a>
        </section>
    </nav>

    <div class="shrink-0 border-t border-[var(--admin-border)] p-3">
        @php($adminUser = auth()->user())
        <div class="rounded-2xl bg-[var(--admin-surface)] p-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--admin-text)] text-xs font-bold text-white">
                    {{ mb_substr($adminUser?->name ?: 'A', 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-semibold text-[var(--admin-text)]">{{ $adminUser?->name ?: 'مدیر فروشگاه' }}</p>
                    <p class="mt-0.5 truncate text-[10px] text-[var(--admin-muted)]">حساب مدیر</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" aria-label="خروج از حساب" title="خروج از حساب" class="flex h-8 w-8 items-center justify-center rounded-xl text-[var(--admin-muted)] transition hover:bg-white hover:text-[var(--admin-danger)]">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-6-3 3m0 0 3 3m-3-3h8.25" /></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>
