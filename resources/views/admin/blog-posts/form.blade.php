@csrf
@php
    $categoryTerms = collect($termOptions)->where('type', 'category')->values();
    $tagTerms = collect($termOptions)->where('type', 'tag')->values();
@endphp

<div class="grid gap-6 2xl:grid-cols-[minmax(0,1fr)_360px]">
    <div class="min-w-0 space-y-6">
        <section class="admin-card space-y-5 p-5 sm:p-7">
            <div>
                <h2 class="text-base font-bold text-[var(--admin-text)]">محتوای مقاله</h2>
                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">ویرایشگر سبک داخلی است؛ بدون بارگذاری کتابخانه‌ی سنگین یا سرویس خارجی. خروجی HTML قبل از ذخیره با فهرست مجاز تگ‌ها پاک‌سازی می‌شود.</p>
            </div>

            <label class="block text-sm text-[var(--admin-text)]">عنوان مقاله
                <input name="title" value="{{ old('title', $post->title) }}" maxlength="255" required data-seo-title class="admin-input mt-2 w-full">
                @error('title')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm text-[var(--admin-text)]">Slug انگلیسی
                <input name="slug" value="{{ old('slug', $post->slug) }}" maxlength="180" dir="ltr" placeholder="choosing-the-right-kitchen-hood" class="admin-input mt-2 w-full text-left">
                <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">کوتاه، خوانا و انگلیسی باشد. اگر هنگام ویرایش تغییر کند، سیستم به‌صورت خودکار Redirect 301 می‌سازد.</span>
                @error('slug')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm text-[var(--admin-text)]">خلاصه / توضیح کارت
                <textarea name="excerpt" rows="3" maxlength="600" data-seo-excerpt class="admin-input mt-2 w-full">{{ old('excerpt', $post->excerpt) }}</textarea>
                @error('excerpt')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <div data-blog-rich-editor class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <label for="blog-body-source" class="text-sm font-semibold text-[var(--admin-text)]">متن مقاله</label>
                    <span class="text-xs text-[var(--admin-muted)]"><span data-editor-word-count>۰</span> واژه</span>
                </div>

                <div class="flex flex-wrap gap-2 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-2" role="toolbar" aria-label="ابزارهای ویرایش متن">
                    <button type="button" data-editor-command="bold" class="admin-btn admin-btn-secondary px-3 py-2" aria-label="درشت">B</button>
                    <button type="button" data-editor-command="italic" class="admin-btn admin-btn-secondary px-3 py-2" aria-label="ایتالیک"><em>I</em></button>
                    <button type="button" data-editor-command="underline" class="admin-btn admin-btn-secondary px-3 py-2" aria-label="زیرخط"><u>U</u></button>
                    <select data-editor-block class="admin-select min-w-28" aria-label="نوع تیتر">
                        <option value="">پاراگراف / تیتر</option>
                        <option value="p">پاراگراف</option>
                        <option value="h2">تیتر H2</option>
                        <option value="h3">تیتر H3</option>
                        <option value="h4">تیتر H4</option>
                        <option value="blockquote">نقل‌قول</option>
                    </select>
                    <button type="button" data-editor-command="insertUnorderedList" class="admin-btn admin-btn-secondary px-3 py-2">فهرست نقطه‌ای</button>
                    <button type="button" data-editor-command="insertOrderedList" class="admin-btn admin-btn-secondary px-3 py-2">فهرست شماره‌ای</button>
                    <button type="button" data-editor-command="createLink" class="admin-btn admin-btn-secondary px-3 py-2">افزودن لینک</button>
                    <button type="button" data-editor-insert-image class="admin-btn admin-btn-secondary px-3 py-2">افزودن با نشانی</button>
                    <button type="button" data-editor-command="undo" class="admin-btn admin-btn-ghost px-3 py-2">Undo</button>
                    <button type="button" data-editor-command="redo" class="admin-btn admin-btn-ghost px-3 py-2">Redo</button>
                </div>

                <details class="overflow-hidden rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)]" data-editor-media-picker>
                    <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-xs font-semibold text-[var(--admin-text)]">
                        <span>انتخاب تصویر از کتابخانه رسانه</span>
                        <span class="rounded-full bg-[var(--admin-white)] px-2.5 py-1 text-[10px] font-medium text-[var(--admin-muted)]">{{ count($mediaImages) }} تصویر اخیر</span>
                    </summary>
                    <div class="border-t border-[var(--admin-border)] p-3">
                        @if(count($mediaImages))
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                                @foreach($mediaImages as $mediaImage)
                                    <button
                                        type="button"
                                        data-editor-media-image
                                        data-media-url="{{ '/storage/' . ltrim($mediaImage->path, '/') }}"
                                        data-media-alt="{{ $mediaImage->alt ?: $mediaImage->original_name }}"
                                        class="group overflow-hidden rounded-xl border border-[var(--admin-border)] bg-[var(--admin-white)] text-right transition hover:border-[var(--admin-accent)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--admin-accent)]"
                                        aria-label="درج تصویر {{ $mediaImage->alt ?: $mediaImage->original_name }}"
                                    >
                                        <span class="block aspect-[4/3] overflow-hidden bg-[var(--admin-surface)]">
                                            <img src="{{ $mediaImage->url }}" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                        </span>
                                        <span class="block truncate px-2.5 py-2 text-[10px] font-medium text-[var(--admin-text-soft)]">{{ $mediaImage->original_name }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-white)] px-4 py-7 text-center">
                                <p class="text-xs font-semibold text-[var(--admin-text)]">کتابخانه هنوز تصویر ندارد.</p>
                                <a href="{{ route('admin.media.create') }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary mt-3">آپلود تصویر جدید</a>
                            </div>
                        @endif
                        <p class="mt-3 text-[10px] leading-5 text-[var(--admin-muted)]">با انتخاب هر تصویر، آن را در محل نشانگر متن درج می‌کنیم. متن Alt از اطلاعات رسانه برداشته می‌شود و بعداً هم می‌توانی ویرایشش کنی.</p>
                    </div>
                </details>

                <textarea id="blog-body-source" name="body" data-editor-source rows="18" required class="admin-input w-full leading-8">{{ old('body', $post->body_html ?: $post->body) }}</textarea>
                <div data-editor-surface hidden class="admin-blog-editor" aria-label="متن مقاله">{!! $editorHtml !!}</div>
                <p class="text-xs leading-6 text-[var(--admin-muted)]">درج تصویر با آدرس HTTPS یا فایل داخلی انجام می‌شود. برای هر تصویر Alt توصیفی بنویسید. کدهای script، style و iframe اجازه ذخیره ندارند.</p>
                @error('body')<span class="block text-xs text-red-600">{{ $message }}</span>@enderror
            </div>
        </section>

        <section class="admin-card space-y-5 p-5 sm:p-7">
            <div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-bold text-[var(--admin-text)]">SEO اختصاصی مقاله</h2>
                    <span class="admin-badge admin-badge-neutral">تحلیل محتوایی داخلی</span>
                </div>
                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">امتیاز فقط یک چک‌لیست تحریریه است و تضمین رتبه یا جایگزین داده‌های Search Console نیست.</p>
            </div>

            <label class="block text-sm text-[var(--admin-text)]">عبارت کلیدی اصلی
                <input name="focus_keyword" value="{{ old('focus_keyword', $post->focus_keyword) }}" maxlength="160" data-seo-keyword class="admin-input mt-2 w-full" placeholder="مثلاً انتخاب هود آشپزخانه">
                @error('focus_keyword')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm text-[var(--admin-text)]">عنوان SEO
                <input name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" maxlength="255" data-seo-meta-title class="admin-input mt-2 w-full">
                <span class="mt-1 flex justify-between gap-2 text-[11px] text-[var(--admin-muted)]"><span>عنوان اختصاصی نتیجه جست‌وجو</span><span data-seo-title-count>۰ / ۶۰</span></span>
                @error('meta_title')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm text-[var(--admin-text)]">توضیحات متا
                <textarea name="meta_description" rows="3" maxlength="320" data-seo-meta-description class="admin-input mt-2 w-full">{{ old('meta_description', $post->meta_description) }}</textarea>
                <span class="mt-1 flex justify-between gap-2 text-[11px] text-[var(--admin-muted)]"><span>توضیح دقیق و متمایز برای همین مقاله</span><span data-seo-description-count>۰ / ۱۶۰</span></span>
                @error('meta_description')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <label class="block text-sm text-[var(--admin-text)]">Canonical اختصاصی (اختیاری)
                <input name="canonical_url" value="{{ old('canonical_url', $post->canonical_url) }}" maxlength="2048" type="url" inputmode="url" dir="ltr" placeholder="https://farajihome.com/blog/article-slug" class="admin-input mt-2 w-full text-left">
                <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">خالی باشد، آدرس اصلی مقاله خودکار استفاده می‌شود. فقط HTTPS پذیرفته می‌شود.</span>
                @error('canonical_url')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm text-[var(--admin-text)]">قانون ربات‌ها
                    <select name="seo_robots" class="admin-select mt-2 w-full">
                        @foreach (['inherit' => 'ارث‌بری از سایت', 'index,follow' => 'Index, Follow', 'noindex,follow' => 'Noindex, Follow', 'noindex,nofollow' => 'Noindex, Nofollow'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('seo_robots', $post->seo_robots ?: 'inherit') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('seo_robots')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm text-[var(--admin-text)]">نوع Schema
                    <select name="schema_type" class="admin-select mt-2 w-full">
                        @foreach (['BlogPosting' => 'BlogPosting', 'Article' => 'Article', 'NewsArticle' => 'NewsArticle'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('schema_type', $post->schema_type ?: 'BlogPosting') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm text-[var(--admin-text)]">عنوان Open Graph
                    <input name="og_title" value="{{ old('og_title', $post->og_title) }}" maxlength="255" class="admin-input mt-2 w-full">
                </label>
                <label class="block text-sm text-[var(--admin-text)]">توضیحات Open Graph
                    <textarea name="og_description" rows="2" maxlength="320" class="admin-input mt-2 w-full">{{ old('og_description', $post->og_description) }}</textarea>
                </label>
            </div>

            <div class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4 sm:p-5" data-blog-seo-analysis>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold text-[var(--admin-text)]">امتیاز چک‌لیست SEO</p>
                        <p class="mt-1 text-[11px] text-[var(--admin-muted)]">راهنمای بهبود، نه پیش‌بینی رتبه</p>
                    </div>
                    <div class="text-2xl font-extrabold tabular-nums text-[var(--admin-accent)]"><span data-seo-score>{{ $seoReport['score'] }}</span><span class="text-sm">/100</span></div>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-[var(--admin-border)]">
                    <div data-seo-score-bar class="h-full rounded-full bg-[var(--admin-accent)] transition-all" style="width: {{ $seoReport['score'] }}%"></div>
                </div>
                <p class="mt-3 text-xs text-[var(--admin-muted)]"><span data-seo-word-count>{{ number_format($seoReport['word_count']) }}</span> واژه · حدود {{ $seoReport['reading_time'] }} دقیقه مطالعه</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach($seoReport['checks'] as $check)
                        <div class="flex items-start gap-2 text-xs" data-seo-check="{{ $check['key'] }}" data-passed="{{ $check['passed'] ? '1' : '0' }}">
                            <span data-check-icon class="{{ $check['passed'] ? 'text-emerald-600' : 'text-amber-600' }}">{{ $check['passed'] ? '✓' : '!' }}</span>
                            <span class="min-w-0"><strong class="block text-[var(--admin-text)]">{{ $check['label'] }}</strong><span class="mt-1 block leading-5 text-[var(--admin-muted)]">{{ $check['detail'] }}</span></span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-[var(--admin-border)] bg-white p-4" dir="ltr">
                <p class="text-[11px] text-slate-500">{{ config('app.url') }}/blog/{{ $post->slug ?: 'article-slug' }}</p>
                <p data-seo-preview-title class="mt-1 line-clamp-2 text-lg font-medium text-blue-800">{{ old('meta_title', $post->meta_title ?: $post->title) }}</p>
                <p data-seo-preview-description class="mt-1 line-clamp-3 text-sm leading-6 text-slate-600">{{ old('meta_description', $post->meta_description ?: $post->excerpt) }}</p>
            </div>
        </section>
    </div>

    <aside class="min-w-0 space-y-6">
        <section class="admin-card space-y-5 p-5">
            <h2 class="text-sm font-bold text-[var(--admin-text)]">انتشار و وضعیت مقاله</h2>
            <label class="block text-sm text-[var(--admin-text)]">وضعیت
                <select name="status" class="admin-select mt-2 w-full" required>
                    <option value="draft" @selected(old('status', $post->status ?? 'draft') === 'draft')>پیش‌نویس</option>
                    <option value="published" @selected(old('status', $post->status ?? 'draft') === 'published')>انتشار / زمان‌بندی</option>
                    @if($post->exists)
                        <option value="trash" @selected(old('status', $post->status) === 'trash')>زباله‌دان</option>
                    @endif
                </select>
                @error('status')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="block text-sm text-[var(--admin-text)]">زمان انتشار
                <input type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}" class="admin-input mt-2 w-full" dir="ltr">
                <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">برای زمان‌بندی، وضعیت را منتشرشده و زمان آینده انتخاب کنید.</span>
                @error('published_at')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="flex items-start gap-3 rounded-xl border border-[var(--admin-border)] p-3 text-sm text-[var(--admin-text)]">
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $post->is_featured) ) class="admin-checkbox mt-0.5 h-4 w-4">
                مقاله ویژه
            </label>
        </section>

        <section class="admin-card space-y-4 p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-bold text-[var(--admin-text)]">دسته‌بندی‌ها</h2>
                <a href="{{ route('admin.blog-terms.create', ['type' => 'category']) }}" class="text-xs font-semibold text-[var(--admin-accent)] underline">مدیریت</a>
            </div>
            @forelse($categoryTerms as $term)
                <label class="flex items-center gap-2 text-sm text-[var(--admin-text)]">
                    <input type="checkbox" name="term_ids[]" value="{{ $term->id }}" @checked(in_array($term->id, old('term_ids', $selectedTermIds))) class="admin-checkbox h-4 w-4">
                    {{ $term->name }}
                </label>
            @empty
                <p class="text-xs leading-6 text-[var(--admin-muted)]">هنوز دسته‌بندی مقاله ایجاد نشده است.</p>
            @endforelse
            @error('term_ids')<span class="block text-xs text-red-600">{{ $message }}</span>@enderror
        </section>

        <section class="admin-card space-y-4 p-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-sm font-bold text-[var(--admin-text)]">برچسب‌ها</h2>
                <a href="{{ route('admin.blog-terms.create', ['type' => 'tag']) }}" class="text-xs font-semibold text-[var(--admin-accent)] underline">مدیریت</a>
            </div>
            <div class="flex flex-wrap gap-2">
                @forelse($tagTerms as $term)
                    <label class="flex items-center gap-2 rounded-lg border border-[var(--admin-border)] px-3 py-2 text-xs text-[var(--admin-text)]">
                        <input type="checkbox" name="term_ids[]" value="{{ $term->id }}" @checked(in_array($term->id, old('term_ids', $selectedTermIds))) class="admin-checkbox h-3.5 w-3.5">
                        {{ $term->name }}
                    </label>
                @empty
                    <p class="text-xs leading-6 text-[var(--admin-muted)]">هنوز برچسبی ایجاد نشده است.</p>
                @endforelse
            </div>
        </section>

        <section class="admin-card space-y-4 p-5">
            <div>
                <h2 class="text-sm font-bold text-[var(--admin-text)]">تصویر شاخص مقاله</h2>
                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">یک تصویر برای کارت مجله و سربرگ مقاله؛ کادر ۱۶:۹ را دقیق تنظیم کن.</p>
            </div>

            <div class="admin-image-preview aspect-video">
                <img
                    src="{{ $post->cover_image_url ?: '' }}"
                    alt="{{ $post->cover_image_alt ?: $post->title }}"
                    data-image-editor-existing-preview
                    @if(! $post->cover_image_url) class="hidden" @endif
                    loading="lazy"
                    decoding="async"
                >
                <div data-image-editor-placeholder class="{{ $post->cover_image_url ? 'hidden' : 'flex' }} h-full flex-col items-center justify-center gap-2 bg-[var(--admin-surface-soft)] p-4 text-center">
                    <span class="text-3xl text-[var(--admin-muted)]">F.</span>
                    <span class="text-xs text-[var(--admin-muted)]">هنوز تصویر شاخص انتخاب نشده است.</span>
                </div>
            </div>

            <label class="block text-sm text-[var(--admin-text)]">انتخاب تصویر جدید
                <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" data-image-editor-input data-image-editor-min-width="800" data-image-editor-min-height="450" data-image-editor-default-ratio="16:9" class="admin-input mt-2 w-full p-2">
            </label>

            <div data-image-editor-panel hidden class="space-y-4 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                <div>
                    <h3 class="text-xs font-bold text-[var(--admin-text)]">تنظیم قاب تصویر</h3>
                    <p class="mt-1 text-[11px] leading-5 text-[var(--admin-muted)]">برای جابه‌جایی تصویر، روی پیش‌نمایش بکش؛ زوم و چرخش هم در دسترس است.</p>
                </div>
                <canvas data-image-editor-canvas role="img" aria-label="پیش‌نمایش برش تصویر شاخص" class="mx-auto block max-w-full rounded-xl bg-[var(--admin-bg)]" style="width:min(100%,520px);height:auto;aspect-ratio:16/9;touch-action:none;cursor:grab;"></canvas>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-xs text-[var(--admin-text)]">نسبت کادر
                        <select data-image-editor-ratio class="admin-select mt-2 w-full"><option value="16:9" selected>عریض ۱۶:۹</option><option value="4:3">استاندارد ۴:۳</option><option value="1:1">مربع ۱:۱</option><option value="4:5">پرتره ۴:۵</option></select>
                    </label>
                    <label class="block text-xs text-[var(--admin-text)]">بزرگ‌نمایی <span data-image-editor-zoom-label>۱×</span>
                        <input data-image-editor-zoom type="range" min="1" max="3" step="0.05" value="1" class="mt-3 w-full">
                    </label>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-image-editor-rotate="-90" class="admin-btn admin-btn-secondary">چرخش پادساعت‌گرد</button>
                    <button type="button" data-image-editor-rotate="90" class="admin-btn admin-btn-secondary">چرخش ساعت‌گرد</button>
                    <button type="button" data-image-editor-reset class="admin-btn admin-btn-ghost">بازنشانی</button>
                </div>
                <p data-image-editor-status class="text-xs text-[var(--admin-muted)]" aria-live="polite">کادر را تنظیم کن.</p>
            </div>

            <label class="block text-sm text-[var(--admin-text)]">متن جایگزین تصویر (Alt)
                <input name="cover_image_alt" value="{{ old('cover_image_alt', $post->cover_image_alt) }}" maxlength="255" class="admin-input mt-2 w-full">
            </label>
            <p class="text-xs leading-6 text-[var(--admin-muted)]">JPG، PNG یا WEBP؛ خروجی برش حداقل ۸۰۰×۴۵۰؛ حداکثر ۸ مگابایت.</p>
            @error('cover_image')<span class="block text-xs text-red-600">{{ $message }}</span>@enderror
            @error('cover_image_alt')<span class="block text-xs text-red-600">{{ $message }}</span>@enderror
        </section>

        <button type="submit" class="admin-btn admin-btn-primary w-full">{{ isset($isEditing) && $isEditing ? 'ذخیره تغییرات' : 'ذخیره مقاله' }}</button>
        <a href="{{ route('admin.blog-posts.index') }}" class="admin-btn admin-btn-ghost w-full">بازگشت به بلاگ</a>
        @if($post->exists && $post->status === 'published' && $post->published_at?->isPast())
            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary w-full">مشاهده صفحه عمومی</a>
        @endif
    </aside>
</div>
