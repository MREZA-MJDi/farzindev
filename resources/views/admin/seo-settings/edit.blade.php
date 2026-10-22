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

            <section class="admin-card space-y-5 p-5 sm:p-7">
                <div>
                    <h2 class="text-base font-bold text-[var(--admin-text)]">۲. کنترل ایندکس و robots.txt</h2>
                    <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">حالت آماده‌سازی، meta ربات‌ها را به noindex, nofollow, noarchive, nosnippet, noimageindex تغییر می‌دهد. فایل robots همچنان اجازهٔ Crawl می‌دهد تا خزنده بتواند meta noindex را بخواند.</p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="flex items-start gap-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950 sm:col-span-2">
                        <input type="hidden" name="seo_development_mode" value="0">
                        <input type="checkbox" name="seo_development_mode" value="1" @checked((string) old('seo_development_mode', $settings['seo_development_mode']) === '1') class="admin-checkbox mt-0.5 h-4 w-4">
                        جلوگیری کامل از ایندکس عمومی تا زمان انتشار
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
                    انتشار نقشهٔ سایت در مسیر sitemap.xml و معرفی آن به خزنده‌ها
                </label>
            </section>

            <section class="admin-card space-y-5 p-5 sm:p-7">
                <div>
                    <h2 class="text-base font-bold text-[var(--admin-text)]">۳. تصویر اشتراک‌گذاری و تأیید سایت</h2>
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
                    <h2 class="text-base font-bold text-[var(--admin-text)]">۴. اطلاعات کسب‌وکار برای JSON-LD</h2>
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

            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="max-w-2xl text-xs leading-6 text-[var(--admin-muted)]">پس از ذخیره، کش تنظیمات و sitemap باطل می‌شود. این صفحه تنظیمات تکنیکی را کنترل می‌کند؛ رتبه یا ایندکس‌شدن در گوگل تضمین‌پذیر نیست.</p>
                <button type="submit" class="admin-btn admin-btn-primary">ذخیره تنظیمات SEO</button>
            </div>
        </form>
    </div>
@endsection
