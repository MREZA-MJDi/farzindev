@extends('admin.layouts.app')

@section('title', 'تنظیمات SEO')
@section('page_title', 'سئو و ایندکس')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div>
            <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-[var(--admin-accent)]">SEO / SEARCH VISIBILITY</p>
            <h1 class="admin-title mt-2">تنظیمات سئو و ایندکس</h1>
            <p class="admin-subtitle mt-2">این تنظیمات مستقیماً روی metaهای عمومی، canonical، robots.txt، sitemap و داده‌های ساختاریافته اعمال می‌شوند.</p>
        </div>

        @php
            $indexingBlocked = ($settings['seo_development_mode'] ?? '1') === '1'
                || ($settings['seo_robots_noindex'] ?? '0') === '1'
                || ($settings['seo_robots_nofollow'] ?? '0') === '1';
        @endphp

        <section class="admin-card space-y-4 p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-[var(--admin-text)]">وضعیت فعلی حضور در گوگل</h2>
                    <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">تغییرات این پنل پس از ذخیره روی robots، metaها و sitemap عمومی سایت اعمال می‌شوند.</p>
                </div>
                <span class="rounded-full px-3 py-2 text-xs font-semibold {{ $indexingBlocked ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $indexingBlocked ? 'ایندکس محدود شده' : 'ایندکس و دنبال‌کردن لینک‌ها فعال' }}
                </span>
            </div>
            <p class="text-xs leading-6 text-[var(--admin-muted)]">
                قفل پیش‌انتشار به‌صورت پیش‌فرض روشن است؛ بنابراین همه صفحات عمومی با
                <span dir="ltr" class="font-mono">noindex, nofollow</span>
                پاسخ می‌گیرند و نقشه سایت منتشر نمی‌شود. مسئول SEO می‌تواند پس از آماده‌شدن اطلاعات واقعی، قفل را از همین صفحه خاموش کند.
                <span class="mx-1">·</span>
                @if (($settings['seo_development_mode'] ?? '1') === '1')
                    <span class="font-semibold text-amber-700">sitemap.xml تا زمان انتشار غیرفعال است.</span>
                @elseif (($settings['seo_sitemap_enabled'] ?? '1') === '1')
                    <a href="{{ route('sitemap') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-[var(--admin-accent)] underline">مشاهده sitemap.xml</a>
                @else
                    <span class="text-[var(--admin-muted)]">sitemap.xml خاموش است.</span>
                @endif
                · فایل ربات‌ها:
                <a href="{{ route('robots') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-[var(--admin-accent)] underline">مشاهده robots.txt</a>
            </p>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('admin.products.index') }}" class="rounded-xl border border-[var(--admin-border)] p-4 transition hover:border-[var(--admin-accent)] hover:bg-[var(--admin-surface)]">
                    <strong class="block text-sm text-[var(--admin-text)]">SEO محصولات</strong>
                    <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">عنوان و توضیحات اختصاصی هر محصول</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-[var(--admin-border)] p-4 transition hover:border-[var(--admin-accent)] hover:bg-[var(--admin-surface)]">
                    <strong class="block text-sm text-[var(--admin-text)]">SEO دسته‌بندی‌ها</strong>
                    <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">عنوان و توضیحات اختصاصی دسته‌ها</span>
                </a>
                <a href="{{ route('admin.blog-posts.index') }}" class="rounded-xl border border-[var(--admin-border)] p-4 transition hover:border-[var(--admin-accent)] hover:bg-[var(--admin-surface)]">
                    <strong class="block text-sm text-[var(--admin-text)]">SEO مقالات</strong>
                    <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">عنوان، توضیحات و زمان انتشار</span>
                </a>
                <a href="{{ route('admin.blog-terms.index') }}" class="rounded-xl border border-[var(--admin-border)] p-4 transition hover:border-[var(--admin-accent)] hover:bg-[var(--admin-surface)]">
                    <strong class="block text-sm text-[var(--admin-text)]">دسته‌ها و برچسب‌های بلاگ</strong>
                    <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">آرشیوهای موضوعی و SEO مستقل هر دسته/برچسب</span>
                </a>
                <a href="{{ route('admin.seo-redirects.index') }}" class="rounded-xl border border-[var(--admin-border)] p-4 transition hover:border-[var(--admin-accent)] hover:bg-[var(--admin-surface)]">
                    <strong class="block text-sm text-[var(--admin-text)]">مدیریت Redirectها</strong>
                    <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">ریدایرکت 301/302، تغییر slug و شمارش استفاده</span>
                </a>
                <a href="{{ route('admin.storefront-settings.edit') }}" class="rounded-xl border border-[var(--admin-border)] p-4 transition hover:border-[var(--admin-accent)] hover:bg-[var(--admin-surface)]">
                    <strong class="block text-sm text-[var(--admin-text)]">اطلاعات تماس و نقشه</strong>
                    <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">آدرس واقعی، مختصات Google Maps و راه‌های ارتباطی</span>
                </a>
            </div>
            <a href="https://search.google.com/search-console" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-xs font-semibold text-[var(--admin-accent)] underline">
                اتصال و بررسی سایت در Google Search Console ↗
            </a>
        </section>

        @if (session('success'))
            <div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.seo-settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="admin-card space-y-5 p-5 sm:p-7">
                <div>
                    <h2 class="text-base font-bold text-[var(--admin-text)]">۱. عنوان، توضیحات و دامنه</h2>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">عنوان و توضیح اختصاصی هر صفحه حفظ می‌شود. مقادیر زیر، پیش‌فرض سراسری و مبنای دامنهٔ canonical هستند.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="block text-sm text-[var(--admin-text)]">
                        نام سایت
                        <input name="seo_site_name" value="{{ old('seo_site_name', $settings['seo_site_name']) }}" required maxlength="100" class="admin-input mt-2 w-full">
                    </label>
                    <label class="block text-sm text-[var(--admin-text)]">
                        دامنهٔ canonical (فقط ریشهٔ سایت)
                        <input name="seo_canonical_base_url" value="{{ old('seo_canonical_base_url', $settings['seo_canonical_base_url']) }}" type="url" required maxlength="255" placeholder="https://farajihome.com" class="admin-input mt-2 w-full" dir="ltr">
                    </label>
                </div>

                <label class="block text-sm text-[var(--admin-text)]">
                    عنوان SEO پیش‌فرض
                    <input name="seo_default_title" value="{{ old('seo_default_title', $settings['seo_default_title']) }}" required maxlength="255" class="admin-input mt-2 w-full">
                </label>

                <label class="block text-sm text-[var(--admin-text)]">
                    توضیحات متا
                    <textarea name="seo_default_description" rows="3" required maxlength="320" class="admin-input mt-2 w-full">{{ old('seo_default_description', $settings['seo_default_description']) }}</textarea>
                </label>

                <label class="block text-sm text-[var(--admin-text)]">
                    کلمات کلیدی پیش‌فرض
                    <textarea name="seo_default_keywords" rows="2" maxlength="1000" class="admin-input mt-2 w-full">{{ old('seo_default_keywords', $settings['seo_default_keywords']) }}</textarea>
                    <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">برای سازگاری قابل تنظیم است؛ گوگل meta keywords را عامل رتبه‌بندی نمی‌داند.</span>
                </label>
            </section>

            @php
                $managedSeoPages = [
                    ['key' => 'home', 'label' => 'صفحه اصلی', 'route' => route('home'), 'title' => 'seo_home_title', 'description' => 'seo_home_description'],
                    ['key' => 'shop', 'label' => 'فروشگاه', 'route' => route('shop.index'), 'title' => 'seo_shop_title', 'description' => 'seo_shop_description'],
                    ['key' => 'categories', 'label' => 'فهرست دسته‌بندی‌ها', 'route' => route('categories.index'), 'title' => 'seo_categories_title', 'description' => 'seo_categories_description'],
                    ['key' => 'blog', 'label' => 'فهرست مقالات', 'route' => route('blog.index'), 'title' => 'seo_blog_title', 'description' => 'seo_blog_description'],
                    ['key' => 'about', 'label' => 'درباره ما', 'route' => route('about'), 'title' => 'seo_about_title', 'description' => 'seo_about_description'],
                    ['key' => 'contact', 'label' => 'تماس با ما', 'route' => route('contact'), 'title' => 'seo_contact_title', 'description' => 'seo_contact_description'],
                ];
            @endphp

            <section class="admin-card space-y-5 p-5 sm:p-7">
                <div>
                    <h2 class="text-base font-bold text-[var(--admin-text)]">۲. سئوی اختصاصی صفحه‌های اصلی</h2>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                        این فیلدها مستقیماً عنوان و توضیحات متای چهار صفحه عمومی را کنترل می‌کنند. خالی‌گذاشتن هر فیلد یعنی استفاده از عنوان داخل صفحه یا مقدار پیش‌فرض سراسری. عنوان محصول، دسته‌بندی و مقاله همچنان از فرم اختصاصی همان محتوا مدیریت می‌شود.
                    </p>
                </div>

                <div class="grid gap-4 xl:grid-cols-2">
                    @foreach($managedSeoPages as $page)
                        <article data-seo-page-preview class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4 sm:p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-sm font-bold text-[var(--admin-text)]">{{ $page['label'] }}</h3>
                                <a href="{{ $page['route'] }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-[var(--admin-accent)] underline">مشاهده صفحه ↗</a>
                            </div>

                            <label class="block text-sm text-[var(--admin-text)]">
                                عنوان SEO
                                <input
                                    name="{{ $page['title'] }}"
                                    value="{{ old($page['title'], $settings[$page['title']] ?? '') }}"
                                    maxlength="255"
                                    data-seo-preview-title
                                    class="admin-input mt-2 w-full"
                                    placeholder="{{ $settings['seo_default_title'] }}"
                                >
                                <span class="mt-1 flex justify-between gap-3 text-[11px] text-[var(--admin-muted)]">
                                    <span>هدف پیشنهادی: حدود ۵۰ تا ۶۰ نویسه</span>
                                    <span data-seo-title-count>۰ / ۲۵۵</span>
                                </span>
                            </label>

                            <label class="mt-4 block text-sm text-[var(--admin-text)]">
                                توضیحات متا
                                <textarea
                                    name="{{ $page['description'] }}"
                                    rows="3"
                                    maxlength="320"
                                    data-seo-preview-description
                                    class="admin-input mt-2 w-full"
                                    placeholder="{{ $settings['seo_default_description'] }}"
                                >{{ old($page['description'], $settings[$page['description']] ?? '') }}</textarea>
                                <span class="mt-1 flex justify-between gap-3 text-[11px] text-[var(--admin-muted)]">
                                    <span>توضیح یکتا و مرتبط با محتوای همین صفحه</span>
                                    <span data-seo-description-count>۰ / ۳۲۰</span>
                                </span>
                            </label>

                            <div class="mt-4 rounded-xl border border-[var(--admin-border)] bg-white p-4" dir="ltr">
                                <p class="text-[11px] text-slate-500">{{ parse_url($page['route'], PHP_URL_HOST) }}{{ parse_url($page['route'], PHP_URL_PATH) }}</p>
                                <p data-seo-preview-title-output class="mt-1 line-clamp-2 text-lg font-medium text-blue-800">{{ old($page['title'], $settings[$page['title']] ?? $settings['seo_default_title']) }}</p>
                                <p data-seo-preview-description-output class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600">{{ old($page['description'], $settings[$page['description']] ?? $settings['seo_default_description']) }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="admin-card space-y-5 p-5 sm:p-7">
                <div>
                    <h2 class="text-base font-bold text-[var(--admin-text)]">۳. کنترل ایندکس و robots.txt</h2>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">قفل پیش‌انتشار همه صفحات عمومی را به noindex, nofollow, noarchive, nosnippet, noimageindex تبدیل می‌کند. robots.txt در این وضعیت عمداً اجازه Crawl می‌دهد تا خزنده بتواند meta noindex را بخواند؛ نقشه سایت نیز تا زمان انتشار از دسترس خارج است.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex items-start gap-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950 sm:col-span-2">
                        <input type="hidden" name="seo_development_mode" value="0">
                        <input type="checkbox" name="seo_development_mode" value="1" @checked((string) old('seo_development_mode', $settings['seo_development_mode']) === '1') class="admin-checkbox mt-0.5 h-4 w-4">
                        قفل انتشار: جلوگیری از ایندکس و دنبال‌کردن لینک‌ها تا تأیید مسئول SEO
                    </label>

                    <label class="flex items-start gap-3 rounded-xl border border-[var(--admin-border)] p-4 text-sm text-[var(--admin-text)] sm:col-span-2">
                        <input type="hidden" name="seo_noindex_shop_filters" value="0">
                        <input type="checkbox" name="seo_noindex_shop_filters" value="1" @checked((string) old('seo_noindex_shop_filters', $settings['seo_noindex_shop_filters'] ?? '1') === '1') class="admin-checkbox mt-0.5 h-4 w-4">
                        noindex برای URLهای جست‌وجو و فیلتر فروشگاه (صفحه اصلی فروشگاه و صفحات دسته‌بندی مستقل ایندکس می‌مانند)
                    </label>

                    @foreach ([
                        'seo_robots_noindex' => 'جلوگیری از ایندکس',
                        'seo_robots_nofollow' => 'دنبال‌نکردن لینک‌ها',
                        'seo_robots_noarchive' => 'عدم نمایش نسخهٔ ذخیره‌شده',
                        'seo_robots_nosnippet' => 'عدم نمایش snippet',
                        'seo_robots_noimageindex' => 'عدم ایندکس تصاویر',
                    ] as $key => $label)
                        <label class="flex items-start gap-3 rounded-xl border border-[var(--admin-border)] p-4 text-sm text-[var(--admin-text)]">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1" @checked((string) old($key, $settings[$key]) === '1') class="admin-checkbox mt-0.5 h-4 w-4">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                <div>
                    <h3 class="text-sm font-bold text-[var(--admin-text)]">دستورهای robots.txt</h3>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">قوانین User-agent، Allow، Disallow و Crawl-delay را وارد کنید. خط Sitemap براساس دامنهٔ canonical و وضعیت نقشهٔ سایت خودکار ساخته می‌شود.</p>
                    <textarea name="seo_robots_txt_rules" rows="6" required maxlength="10000" class="admin-input mt-2 w-full font-mono text-sm" dir="ltr">{{ old('seo_robots_txt_rules', $settings['seo_robots_txt_rules']) }}</textarea>
                    <p class="mt-2 text-xs leading-6 text-amber-700">هشدار: دستور Disallow برای ریشه می‌تواند مانع دیده‌شدن meta noindex شود؛ robots.txt ابزار امنیتی یا کنترل دسترسی نیست.</p>
                </div>

                <label class="flex items-start gap-3 rounded-xl border border-[var(--admin-border)] p-4 text-sm text-[var(--admin-text)]">
                    <input type="hidden" name="seo_sitemap_enabled" value="0">
                    <input type="checkbox" name="seo_sitemap_enabled" value="1" @checked((string) old('seo_sitemap_enabled', $settings['seo_sitemap_enabled']) === '1') class="admin-checkbox mt-0.5 h-4 w-4">
                    انتشار نقشهٔ سایت در مسیر sitemap.xml و معرفی آن به خزنده‌ها (فقط پس از خاموش‌کردن قفل پیش‌انتشار)
                </label>
            </section>

            <section class="admin-card space-y-5 p-5 sm:p-7">
                <div>
                    <h2 class="text-base font-bold text-[var(--admin-text)]">۴. تصویر اشتراک‌گذاری و تأیید سایت</h2>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">تصویر عمومی برای پیش‌نمایش شبکه‌های اجتماعی به‌کار می‌رود. اندازهٔ پیشنهادی حداقل ۱۲۰۰×۶۳۰ و حداکثر حجم ۵ مگابایت است.</p>
                </div>

                <label class="block text-sm text-[var(--admin-text)]">
                    آپلود تصویر Open Graph
                    <input name="seo_default_og_image_upload" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input mt-2 w-full p-2">
                </label>
                @if($ogImageUrl)
                    <div>
                        <p class="mb-2 text-xs text-[var(--admin-muted)]">پیش‌نمایش تصویر فعلی</p>
                        <img src="{{ $ogImageUrl }}" alt="پیش‌نمایش تصویر Open Graph" loading="lazy" class="max-h-64 w-full rounded-xl border border-[var(--admin-border)] object-contain object-right">
                    </div>
                @endif

                <label class="block text-sm text-[var(--admin-text)]">
                    یا آدرس تصویر خارجی (URL کامل)
                    <input name="seo_default_og_image" type="url" value="{{ old('seo_default_og_image', filter_var($settings['seo_default_og_image'], FILTER_VALIDATE_URL) ? $settings['seo_default_og_image'] : '') }}" maxlength="2048" placeholder="https://example.com/seo-cover.webp" class="admin-input mt-2 w-full" dir="ltr">
                </label>

                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="block text-sm text-[var(--admin-text)]">
                        Google Search Console verification
                        <input name="seo_google_site_verification" value="{{ old('seo_google_site_verification', $settings['seo_google_site_verification']) }}" maxlength="255" class="admin-input mt-2 w-full" dir="ltr">
                    </label>
                    <label class="block text-sm text-[var(--admin-text)]">
                        Bing Webmaster verification
                        <input name="seo_bing_site_verification" value="{{ old('seo_bing_site_verification', $settings['seo_bing_site_verification']) }}" maxlength="255" class="admin-input mt-2 w-full" dir="ltr">
                    </label>
                    <label class="block text-sm text-[var(--admin-text)] sm:col-span-2">
                        نام کاربری X / Twitter (اختیاری)
                        <input name="seo_twitter_site" value="{{ old('seo_twitter_site', $settings['seo_twitter_site']) }}" maxlength="16" placeholder="@farzin" class="admin-input mt-2 w-full" dir="ltr">
                    </label>
                </div>
            </section>

            <section class="admin-card space-y-5 p-5 sm:p-7">
                <div>
                    <h2 class="text-base font-bold text-[var(--admin-text)]">۵. اطلاعات کسب‌وکار برای JSON-LD</h2>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">فقط اطلاعات واقعی و قابل‌تأیید را وارد کنید؛ این داده‌ها برای معرفی سازمان در داده‌های ساختاریافته به‌کار می‌روند.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="block text-sm text-[var(--admin-text)]">
                        نام کسب‌وکار
                        <input name="seo_organization_name" value="{{ old('seo_organization_name', $settings['seo_organization_name']) }}" maxlength="255" class="admin-input mt-2 w-full">
                    </label>
                    <label class="block text-sm text-[var(--admin-text)]">
                        شمارهٔ تماس عمومی
                        <input name="seo_organization_phone" value="{{ old('seo_organization_phone', $settings['seo_organization_phone']) }}" maxlength="50" class="admin-input mt-2 w-full" dir="ltr">
                    </label>
                    <label class="block text-sm text-[var(--admin-text)]">
                        ایمیل عمومی
                        <input name="seo_organization_email" type="email" value="{{ old('seo_organization_email', $settings['seo_organization_email']) }}" maxlength="255" class="admin-input mt-2 w-full" dir="ltr">
                    </label>
                    <label class="block text-sm text-[var(--admin-text)]">
                        آدرس لوگو (URL کامل)
                        <input name="seo_organization_logo_url" type="url" value="{{ old('seo_organization_logo_url', $settings['seo_organization_logo_url']) }}" maxlength="2048" class="admin-input mt-2 w-full" dir="ltr">
                    </label>
                    <label class="block text-sm text-[var(--admin-text)] sm:col-span-2">
                        نشانی عمومی کسب‌وکار
                        <textarea name="seo_organization_address" rows="2" maxlength="500" class="admin-input mt-2 w-full">{{ old('seo_organization_address', $settings['seo_organization_address']) }}</textarea>
                    </label>
                </div>
            </section>

            <section class="admin-card space-y-5 p-5 sm:p-7"
                x-data="{ tags: @js(old('seo_custom_meta_tags', $customMetaTags)) }">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-[var(--admin-text)]">۶. تگ‌های meta سفارشی</h2>
                        <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                            برای افزودن تگ‌هایی مثل theme-color یا og:locale:alternate استفاده کنید. تگ‌های اصلی title، description، robots، canonical و Open Graph که سیستم تولید می‌کند، تکرارشدنی نیستند.
                        </p>
                    </div>
                    <button type="button"
                        @click="tags.push({ type: 'name', key: '', content: '' })"
                        class="rounded-xl border border-[var(--admin-border)] px-4 py-2 text-sm font-semibold text-[var(--admin-text)] hover:bg-[var(--admin-surface)]">
                        + افزودن تگ
                    </button>
                </div>

                <p x-show="tags.length === 0" class="rounded-xl border border-dashed border-[var(--admin-border)] p-4 text-sm text-[var(--admin-muted)]">
                    هنوز تگ سفارشی ثبت نشده است.
                </p>

                <template x-for="(tag, index) in tags" :key="index">
                    <div class="grid gap-3 rounded-xl border border-[var(--admin-border)] p-4 sm:grid-cols-12">
                        <label class="block text-sm text-[var(--admin-text)] sm:col-span-2">
                            نوع تگ
                            <select :name="'seo_custom_meta_tags[' + index + '][type]'"
                                x-model="tag.type" class="admin-input mt-2 w-full">
                                <option value="name">name</option>
                                <option value="property">property</option>
                            </select>
                        </label>
                        <label class="block text-sm text-[var(--admin-text)] sm:col-span-4">
                            نام / کلید
                            <input :name="'seo_custom_meta_tags[' + index + '][key]'"
                                x-model="tag.key" maxlength="100" placeholder="theme-color"
                                class="admin-input mt-2 w-full" dir="ltr">
                        </label>
                        <label class="block text-sm text-[var(--admin-text)] sm:col-span-5">
                            محتوا
                            <input :name="'seo_custom_meta_tags[' + index + '][content]'"
                                x-model="tag.content" maxlength="1000" placeholder="#f5f1e9"
                                class="admin-input mt-2 w-full" dir="ltr">
                        </label>
                        <div class="flex items-end sm:col-span-1">
                            <button type="button" @click="tags.splice(index, 1)"
                                class="w-full rounded-xl border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50">
                                حذف
                            </button>
                        </div>
                    </div>
                </template>
            </section>

            <section class="admin-card space-y-5 p-5 sm:p-7"
                x-data="{ links: @js(old('seo_custom_link_tags', $customLinkTags)) }">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-[var(--admin-text)]">۷. تگ‌های link سفارشی</h2>
                        <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                            برای لینک زبان جایگزین (hreflang)، آیکون سایت، manifest، preconnect یا preload استفاده کنید. تگ canonical در این بخش غیرفعال است تا با canonical خودکار تداخل نداشته باشد.
                        </p>
                    </div>
                    <button type="button"
                        @click="links.push({ rel: 'alternate', href: '', hreflang: '', type: '', as: '', crossorigin: '' })"
                        class="rounded-xl border border-[var(--admin-border)] px-4 py-2 text-sm font-semibold text-[var(--admin-text)] hover:bg-[var(--admin-surface)]">
                        + افزودن لینک
                    </button>
                </div>

                <p x-show="links.length === 0" class="rounded-xl border border-dashed border-[var(--admin-border)] p-4 text-sm text-[var(--admin-muted)]">
                    هنوز لینک سفارشی ثبت نشده است.
                </p>

                <template x-for="(link, index) in links" :key="index">
                    <div class="space-y-4 rounded-xl border border-[var(--admin-border)] p-4">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <label class="block text-sm text-[var(--admin-text)]">
                                رابطه (rel)
                                <select :name="'seo_custom_link_tags[' + index + '][rel]'"
                                    x-model="link.rel" class="admin-input mt-2 w-full">
                                    <option value="alternate">alternate (زبان جایگزین)</option>
                                    <option value="icon">icon (آیکون سایت)</option>
                                    <option value="apple-touch-icon">apple-touch-icon</option>
                                    <option value="manifest">manifest</option>
                                    <option value="preconnect">preconnect</option>
                                    <option value="dns-prefetch">dns-prefetch</option>
                                    <option value="preload">preload</option>
                                    <option value="modulepreload">modulepreload</option>
                                    <option value="prefetch">prefetch</option>
                                    <option value="stylesheet">stylesheet</option>
                                    <option value="author">author</option>
                                    <option value="help">help</option>
                                    <option value="license">license</option>
                                    <option value="mask-icon">mask-icon</option>
                                </select>
                            </label>
                            <label class="block text-sm text-[var(--admin-text)] sm:col-span-2">
                                آدرس (href)
                                <input :name="'seo_custom_link_tags[' + index + '][href]'"
                                    x-model="link.href" maxlength="2048"
                                    placeholder="https://example.com/en یا /favicon.svg"
                                    class="admin-input mt-2 w-full" dir="ltr">
                            </label>
                            <label class="block text-sm text-[var(--admin-text)]">
                                زبان جایگزین (اختیاری)
                                <input :name="'seo_custom_link_tags[' + index + '][hreflang]'"
                                    x-model="link.hreflang" maxlength="35" placeholder="en یا fa-IR"
                                    class="admin-input mt-2 w-full" dir="ltr">
                            </label>
                            <label class="block text-sm text-[var(--admin-text)]">
                                نوع فایل (اختیاری)
                                <input :name="'seo_custom_link_tags[' + index + '][type]'"
                                    x-model="link.type" maxlength="100"
                                    placeholder="image/svg+xml یا text/css"
                                    class="admin-input mt-2 w-full" dir="ltr">
                            </label>
                            <label class="block text-sm text-[var(--admin-text)]">
                                نوع منبع برای preload (اختیاری)
                                <select :name="'seo_custom_link_tags[' + index + '][as]'"
                                    x-model="link.as" class="admin-input mt-2 w-full">
                                    <option value="">بدون مقدار</option>
                                    <option value="font">font</option>
                                    <option value="image">image</option>
                                    <option value="script">script</option>
                                    <option value="style">style</option>
                                    <option value="fetch">fetch</option>
                                    <option value="document">document</option>
                                    <option value="audio">audio</option>
                                    <option value="video">video</option>
                                    <option value="worker">worker</option>
                                </select>
                            </label>
                            <label class="block text-sm text-[var(--admin-text)]">
                                crossorigin (اختیاری)
                                <select :name="'seo_custom_link_tags[' + index + '][crossorigin]'"
                                    x-model="link.crossorigin" class="admin-input mt-2 w-full">
                                    <option value="">بدون مقدار</option>
                                    <option value="anonymous">anonymous</option>
                                    <option value="use-credentials">use-credentials</option>
                                </select>
                            </label>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" @click="links.splice(index, 1)"
                                class="rounded-xl border border-red-200 px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                حذف لینک
                            </button>
                        </div>
                    </div>
                </template>
            </section>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="max-w-2xl text-xs leading-6 text-[var(--admin-muted)]">پس از ذخیره، کش تنظیمات و sitemap باطل می‌شود. این صفحه تنظیمات تکنیکی را کنترل می‌کند؛ رتبه یا ایندکس‌شدن در گوگل تضمین‌پذیر نیست.</p>
                <button type="submit" class="admin-btn admin-btn-primary">ذخیره تنظیمات SEO</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-seo-page-preview]').forEach(function (card) {
        const title = card.querySelector('[data-seo-preview-title]');
        const description = card.querySelector('[data-seo-preview-description]');
        const titleOutput = card.querySelector('[data-seo-preview-title-output]');
        const descriptionOutput = card.querySelector('[data-seo-preview-description-output]');
        const titleCount = card.querySelector('[data-seo-title-count]');
        const descriptionCount = card.querySelector('[data-seo-description-count]');

        const update = function () {
            const titleValue = title.value.trim();
            const descriptionValue = description.value.trim();

            titleOutput.textContent = titleValue || title.getAttribute('placeholder') || '';
            descriptionOutput.textContent = descriptionValue || description.getAttribute('placeholder') || '';
            titleCount.textContent = title.value.length + ' / 255';
            descriptionCount.textContent = description.value.length + ' / 320';
        };

        title.addEventListener('input', update);
        description.addEventListener('input', update);
        update();
    });
});
</script>
@endpush
