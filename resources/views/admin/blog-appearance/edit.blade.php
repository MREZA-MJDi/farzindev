@extends('admin.layouts.app')

@section('title', 'ظاهر مجله')
@section('page_title', 'استایل و تصاویر مجله')

@section('content')
    @php
        $hero = $appearance['hero'];
        $closing = $appearance['closing'];
        $positions = ['center' => 'وسط', 'top' => 'بالا', 'bottom' => 'پایین', 'left' => 'چپ', 'right' => 'راست'];
        $slotPreviewStyle = static fn (array $slot): string => 'object-fit:' . $slot['fit'] . ';object-position:' . $slot['object_position'] . ';transform:scale(' . ($slot['scale'] / 100) . ');';
    @endphp

    <div class="space-y-6" data-blog-appearance>
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[var(--admin-accent)]">FARZIN JOURNAL / ART DIRECTION</p>
                <h1 class="admin-title mt-2">استایل و تصاویر مجله</h1>
                <p class="admin-subtitle mt-2 max-w-3xl">تصویر هدر، گرافیک بخش پایانی و عرض چیدمان را کنترل کن. کراپ، پیش‌نمایش و تنظیم جای تصویر همین‌جا انجام می‌شود.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('blog.index') }}" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" /></svg>
                    پیش‌نمایش مجله
                </a>
                <a href="{{ route('admin.media.index') }}" class="admin-btn admin-btn-secondary">کتابخانه رسانه</a>
                <a href="{{ route('admin.blog-posts.index') }}" class="admin-btn admin-btn-primary">مدیریت مقاله‌ها</a>
            </div>
        </header>

        @if(session('success'))
            <div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div role="alert" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <ul class="list-inside list-disc space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <section class="admin-card overflow-hidden">
            <div class="border-b border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-5 py-4 sm:px-7">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[var(--admin-muted)]">01 / MASTHEAD ARTWORK</p>
                <h2 class="mt-2 text-base font-bold text-[var(--admin-text)]">تصویر اصلی بنر اول</h2>
                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">تصویر در مرکز نقوش مدور Hero می‌نشیند. نسبت مربع برای این جایگاه بهینه است؛ بدون تصویر، طرح هندسی فعلی باقی می‌ماند.</p>
            </div>

            <form method="POST" action="{{ route('admin.blog-appearance.update') }}" enctype="multipart/form-data" class="grid gap-6 p-5 xl:grid-cols-[minmax(0,1fr)_minmax(300px,.8fr)] sm:p-7" data-blog-appearance-slot="hero">
                @csrf
                @method('PUT')

                <div class="min-w-0 space-y-5">
                    <div>
                        <label for="hero-art-upload" class="admin-label">آپلود تصویر Hero</label>
                        <input id="hero-art-upload" name="hero_art_image_upload" type="file" accept="image/jpeg,image/png,image/webp" data-image-editor-input data-image-editor-min-width="640" data-image-editor-min-height="640" data-image-editor-default-ratio="1:1" class="admin-input w-full p-2">
                        <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">JPG، PNG یا WEBP؛ حداکثر ۸MB. تصویر در مرورگر کراپ می‌شود و نسخه WebP با ابعاد مناسب ذخیره خواهد شد.</p>
                        @error('hero_art_image_upload')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </div>

                    <div class="admin-image-preview aspect-square max-w-[22rem]">
                        <img
                            src="{{ $hero['image_url'] ?: '' }}"
                            srcset="{{ $hero['image_srcset'] ?: '' }}"
                            alt="{{ $hero['alt'] ?: 'تصویر فعلی بنر مجله' }}"
                            data-image-editor-existing-preview
                            class="admin-image {{ $hero['image_url'] ? '' : 'hidden' }}"
                        >
                        <div data-image-editor-placeholder class="{{ $hero['image_url'] ? 'hidden' : 'flex' }} h-full flex-col items-center justify-center gap-2 border border-dashed border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5 text-center">
                            <span class="text-4xl text-[var(--admin-muted)]">F.</span>
                            <span class="text-xs text-[var(--admin-muted)]">هنوز تصویری برای Hero انتخاب نشده است.</span>
                        </div>
                    </div>

                    <div data-image-editor-panel hidden class="space-y-4 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                        <div>
                            <h3 class="text-sm font-bold text-[var(--admin-text)]">کراپ زنده</h3>
                            <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">کادر را بکش؛ زوم و چرخش را تنظیم کن. خروجی نهایی دقیقاً از همین پیش‌نمایش ساخته می‌شود.</p>
                        </div>
                        <canvas data-image-editor-canvas role="img" aria-label="پیش‌نمایش کراپ تصویر هدر" class="mx-auto block max-w-full rounded-xl bg-[var(--admin-bg)]" style="width:min(100%,520px);height:auto;aspect-ratio:1/1;touch-action:none;cursor:grab;"></canvas>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block text-xs text-[var(--admin-text)]">نسبت کادر
                                <select data-image-editor-ratio class="admin-select mt-2 w-full"><option value="1:1" selected>مربع ۱:۱</option><option value="4:3">استاندارد ۴:۳</option><option value="4:5">پرتره ۴:۵</option><option value="16:9">عریض ۱۶:۹</option></select>
                            </label>
                            <label class="block text-xs text-[var(--admin-text)]">بزرگ‌نمایی <span data-image-editor-zoom-label>۱×</span>
                                <input data-image-editor-zoom type="range" min="1" max="3" step="0.05" value="1" class="mt-3 w-full">
                            </label>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-image-editor-rotate="-90" class="admin-btn admin-btn-secondary">↶ چرخش</button>
                            <button type="button" data-image-editor-rotate="90" class="admin-btn admin-btn-secondary">چرخش ↷</button>
                            <button type="button" data-image-editor-reset class="admin-btn admin-btn-ghost">بازنشانی</button>
                        </div>
                        <p data-image-editor-status class="text-xs text-[var(--admin-muted)]" aria-live="polite">کادر را تنظیم کن.</p>
                    </div>

                    <label class="block text-sm text-[var(--admin-text)]">متن جایگزین تصویر (Alt)
                        <input name="hero_art_alt" value="{{ old('hero_art_alt', $hero['alt']) }}" maxlength="255" class="admin-input mt-2 w-full">
                    </label>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <label class="block text-sm text-[var(--admin-text)]">نوع نمایش
                            <select name="hero_art_fit" data-blog-preview-fit class="admin-select mt-2 w-full">
                                <option value="cover" @selected(old('hero_art_fit', $hero['fit']) === 'cover')>پرکردن قاب</option>
                                <option value="contain" @selected(old('hero_art_fit', $hero['fit']) === 'contain')>نمایش کامل</option>
                            </select>
                        </label>
                        <label class="block text-sm text-[var(--admin-text)]">جای تصویر
                            <select name="hero_art_position" data-blog-preview-position class="admin-select mt-2 w-full">
                                @foreach($positions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('hero_art_position', $hero['position']) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block text-sm text-[var(--admin-text)]">مقیاس <span data-blog-preview-scale-label>{{ old('hero_art_scale', $hero['scale']) }}</span>٪
                            <input name="hero_art_scale" data-blog-preview-scale type="range" min="70" max="135" step="1" value="{{ old('hero_art_scale', $hero['scale']) }}" class="mt-4 w-full">
                        </label>
                    </div>

                    @if($hero['image_url'])
                        <label class="flex items-start gap-3 rounded-xl border border-[var(--admin-border)] p-3 text-sm text-[var(--admin-text)]">
                            <input type="checkbox" name="hero_art_image_remove" value="1" class="admin-checkbox mt-0.5 h-4 w-4">
                            حذف تصویر و بازگشت به نقوش هندسی CSS
                        </label>
                    @endif

                    <div class="flex flex-wrap justify-end gap-2 border-t border-[var(--admin-border)] pt-4">
                        <button type="submit" class="admin-btn admin-btn-primary">ذخیره تصویر و تنظیمات Hero</button>
                    </div>
                </div>

                <div class="min-w-0 space-y-3">
                    <p class="text-xs font-semibold text-[var(--admin-text)]">پیش‌نمایش در قاب واقعی مجله</p>
                    <div class="blog-design-preview blog-design-preview--hero">
                        <div class="blog-design-preview__copy">
                            <span>FARZIN JOURNAL</span>
                            <strong>برای خانه‌ای<br>که به سلیقه‌ی تو فکر شده.</strong>
                            <small>ایده‌های خوب، انتخاب‌های آگاهانه.</small>
                        </div>
                        <div class="blog-design-preview__art" data-blog-slot-live-frame>
                            @if($hero['image_url'])
                                <img data-blog-slot-live-image src="{{ $hero['image_url'] }}" alt="" style="{{ $slotPreviewStyle($hero) }}">
                            @else
                                <div data-blog-slot-live-empty class="blog-design-preview__fallback"><span>F.</span></div>
                                <img data-blog-slot-live-image class="hidden" src="" alt="">
                            @endif
                            <span class="blog-design-preview__ring"></span>
                        </div>
                    </div>
                    <p class="text-[11px] leading-6 text-[var(--admin-muted)]">این پیش‌نمایش تقریبی از قاب Hero است؛ نتیجه‌ی نهایی کراپ در canvas بالا دیده می‌شود. پس از ذخیره، تصویر با تنظیمات مقیاس، جایگاه و fit روی صفحه عمومی رندر می‌شود.</p>
                </div>
            </form>
        </section>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-5 py-4 sm:px-7">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[var(--admin-muted)]">02 / CLOSING ARTWORK</p>
                <h2 class="mt-2 text-base font-bold text-[var(--admin-text)]">تصویر بخش پایانی مجله</h2>
                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">این تصویر کنار دعوت به دیدن محصولات نمایش داده می‌شود. اگر حذفش کنی، نشان F و حلقه‌های سبک CSS جای آن را می‌گیرند.</p>
            </div>

            <form method="POST" action="{{ route('admin.blog-appearance.update') }}" enctype="multipart/form-data" class="grid gap-6 p-5 xl:grid-cols-[minmax(0,1fr)_minmax(300px,.8fr)] sm:p-7" data-blog-appearance-slot="closing">
                @csrf
                @method('PUT')

                <div class="min-w-0 space-y-5">
                    <div>
                        <label for="closing-art-upload" class="admin-label">آپلود تصویر بخش پایانی</label>
                        <input id="closing-art-upload" name="closing_art_image_upload" type="file" accept="image/jpeg,image/png,image/webp" data-image-editor-input data-image-editor-min-width="640" data-image-editor-min-height="640" data-image-editor-default-ratio="1:1" class="admin-input w-full p-2">
                        <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">JPG، PNG یا WEBP؛ مربع ۱:۱؛ حداکثر ۸MB.</p>
                        @error('closing_art_image_upload')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                    </div>

                    <div class="admin-image-preview aspect-square max-w-[22rem]">
                        <img
                            src="{{ $closing['image_url'] ?: '' }}"
                            srcset="{{ $closing['image_srcset'] ?: '' }}"
                            alt="{{ $closing['alt'] ?: 'تصویر فعلی بخش پایانی مجله' }}"
                            data-image-editor-existing-preview
                            class="admin-image {{ $closing['image_url'] ? '' : 'hidden' }}"
                        >
                        <div data-image-editor-placeholder class="{{ $closing['image_url'] ? 'hidden' : 'flex' }} h-full flex-col items-center justify-center gap-2 border border-dashed border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5 text-center">
                            <span class="text-4xl text-[var(--admin-muted)]">F.</span>
                            <span class="text-xs text-[var(--admin-muted)]">تصویر بخش پایانی هنوز انتخاب نشده است.</span>
                        </div>
                    </div>

                    <div data-image-editor-panel hidden class="space-y-4 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                        <strong class="block text-sm font-bold text-[var(--admin-text)]">کراپ و پیش‌نمایش</strong>
                        <canvas data-image-editor-canvas role="img" aria-label="پیش‌نمایش کراپ تصویر بخش پایانی" class="mx-auto block max-w-full rounded-xl bg-[var(--admin-bg)]" style="width:min(100%,520px);height:auto;aspect-ratio:1/1;touch-action:none;cursor:grab;"></canvas>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block text-xs text-[var(--admin-text)]">نسبت کادر
                                <select data-image-editor-ratio class="admin-select mt-2 w-full"><option value="1:1" selected>مربع ۱:۱</option><option value="4:3">استاندارد ۴:۳</option><option value="4:5">پرتره ۴:۵</option><option value="16:9">عریض ۱۶:۹</option></select>
                            </label>
                            <label class="block text-xs text-[var(--admin-text)]">بزرگ‌نمایی <span data-image-editor-zoom-label>۱×</span>
                                <input data-image-editor-zoom type="range" min="1" max="3" step="0.05" value="1" class="mt-3 w-full">
                            </label>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-image-editor-rotate="-90" class="admin-btn admin-btn-secondary">↶ چرخش</button>
                            <button type="button" data-image-editor-rotate="90" class="admin-btn admin-btn-secondary">چرخش ↷</button>
                            <button type="button" data-image-editor-reset class="admin-btn admin-btn-ghost">بازنشانی</button>
                        </div>
                        <p data-image-editor-status class="text-xs text-[var(--admin-muted)]" aria-live="polite">کادر را تنظیم کن.</p>
                    </div>

                    <label class="block text-sm text-[var(--admin-text)]">متن جایگزین تصویر (Alt)
                        <input name="closing_art_alt" value="{{ old('closing_art_alt', $closing['alt']) }}" maxlength="255" class="admin-input mt-2 w-full">
                    </label>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <label class="block text-sm text-[var(--admin-text)]">نوع نمایش
                            <select name="closing_art_fit" data-blog-preview-fit class="admin-select mt-2 w-full">
                                <option value="cover" @selected(old('closing_art_fit', $closing['fit']) === 'cover')>پرکردن قاب</option>
                                <option value="contain" @selected(old('closing_art_fit', $closing['fit']) === 'contain')>نمایش کامل</option>
                            </select>
                        </label>
                        <label class="block text-sm text-[var(--admin-text)]">جای تصویر
                            <select name="closing_art_position" data-blog-preview-position class="admin-select mt-2 w-full">
                                @foreach($positions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('closing_art_position', $closing['position']) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block text-sm text-[var(--admin-text)]">مقیاس <span data-blog-preview-scale-label>{{ old('closing_art_scale', $closing['scale']) }}</span>٪
                            <input name="closing_art_scale" data-blog-preview-scale type="range" min="70" max="135" step="1" value="{{ old('closing_art_scale', $closing['scale']) }}" class="mt-4 w-full">
                        </label>
                    </div>

                    @if($closing['image_url'])
                        <label class="flex items-start gap-3 rounded-xl border border-[var(--admin-border)] p-3 text-sm text-[var(--admin-text)]">
                            <input type="checkbox" name="closing_art_image_remove" value="1" class="admin-checkbox mt-0.5 h-4 w-4">
                            حذف تصویر و بازگشت به طرح CSS
                        </label>
                    @endif

                    <div class="flex justify-end border-t border-[var(--admin-border)] pt-4">
                        <button type="submit" class="admin-btn admin-btn-primary">ذخیره تصویر بخش پایانی</button>
                    </div>
                </div>

                <div class="min-w-0 space-y-3">
                    <p class="text-xs font-semibold text-[var(--admin-text)]">پیش‌نمایش قاب بخش پایانی</p>
                    <div class="blog-design-preview blog-design-preview--closing">
                        <div class="blog-design-preview__copy">
                            <span>THOUGHTFUL CHOICES</span>
                            <strong>الهام بگیر.<br>دقیق‌تر انتخاب کن.</strong>
                            <small>گشت‌وگذار در محصولات ←</small>
                        </div>
                        <div class="blog-design-preview__art" data-blog-slot-live-frame>
                            @if($closing['image_url'])
                                <img data-blog-slot-live-image src="{{ $closing['image_url'] }}" alt="" style="{{ $slotPreviewStyle($closing) }}">
                            @else
                                <div data-blog-slot-live-empty class="blog-design-preview__fallback"><span>F.</span></div>
                                <img data-blog-slot-live-image class="hidden" src="" alt="">
                            @endif
                            <span class="blog-design-preview__ring"></span>
                        </div>
                    </div>
                    <p class="text-[11px] leading-6 text-[var(--admin-muted)]">با انتخاب تصویر جدید، این قاب بلافاصله پیش‌نمایش فایل را نشان می‌دهد؛ canvas کراپ، کادر نهایی فایل ذخیره‌شده را کنترل می‌کند.</p>
                </div>
            </form>
        </section>

        <section class="admin-card space-y-5 p-5 sm:p-7">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[var(--admin-muted)]">03 / GRID & CONTAINER</p>
                <h2 class="mt-2 text-base font-bold text-[var(--admin-text)]">مقیاس صفحه و عرض کانتینر</h2>
                <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">این مقدار فقط عرض محتوای مجله را در نمایشگرهای بزرگ کنترل می‌کند؛ در موبایل کانتینر همچنان به عرض صفحه محدود می‌ماند.</p>
            </div>
            <form method="POST" action="{{ route('admin.blog-appearance.update') }}" class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-center">
                @csrf
                @method('PUT')
                <label class="block text-sm text-[var(--admin-text)]">
                    عرض حداکثری کانتینر
                    <div class="mt-3 flex items-center gap-4">
                        <input name="container_width" data-blog-container-width type="range" min="1080" max="1440" step="8" value="{{ old('container_width', $appearance['container_width']) }}" class="w-full">
                        <strong data-blog-container-width-output class="min-w-20 text-left text-sm tabular-nums text-[var(--admin-accent)]">{{ number_format(old('container_width', $appearance['container_width'])) }}px</strong>
                    </div>
                    <span class="mt-2 block text-xs leading-6 text-[var(--admin-muted)]">پیشنهاد شروع: ۱۲۸۰px؛ برای گرید بازتر تا ۱۴۴۰px افزایش بده.</span>
                </label>
                <div class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                    <p class="mb-3 text-[10px] font-bold text-[var(--admin-muted)]">LIVE GRID PREVIEW</p>
                    <div class="mx-auto w-full rounded-lg border border-dashed border-[var(--admin-accent)] p-2 transition-all" data-blog-container-preview style="max-width:{{ $appearance['container_width'] }}px">
                        <div class="grid grid-cols-3 gap-2">
                            <span class="h-10 rounded bg-[var(--admin-accent-soft)]"></span>
                            <span class="h-10 rounded bg-[var(--admin-accent-soft)]"></span>
                            <span class="h-10 rounded bg-[var(--admin-accent-soft)]"></span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end lg:col-span-2">
                    <button type="submit" class="admin-btn admin-btn-primary">ذخیره عرض کانتینر</button>
                </div>
            </form>
        </section>

        <section class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5">
            <h2 class="text-sm font-bold text-[var(--admin-text)]">تصاویر کارت‌ها و داخل مقاله کجا مدیریت می‌شوند؟</h2>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                <a href="{{ route('admin.blog-posts.index') }}" class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-4 transition hover:border-[var(--admin-accent)]">
                    <strong class="block text-sm text-[var(--admin-text)]">تصویر شاخص مقاله‌ها</strong>
                    <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">همان تصویر روی کارت‌ها، مقاله‌ی منتخب و صفحه‌ی مقاله استفاده می‌شود؛ در فرم مقاله کراپ و Alt دارد.</span>
                </a>
                <a href="{{ route('admin.media.index') }}" class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-4 transition hover:border-[var(--admin-accent)]">
                    <strong class="block text-sm text-[var(--admin-text)]">کتابخانه‌ی رسانه</strong>
                    <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">تصویرها را آپلود، مرور و برای استفاده‌ی دوباره در متن مقاله انتخاب کن.</span>
                </a>
                <a href="{{ route('admin.media.create') }}" class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-white)] p-4 transition hover:border-[var(--admin-accent)]">
                    <strong class="block text-sm text-[var(--admin-text)]">آپلود رسانه‌ی جدید</strong>
                    <span class="mt-1 block text-xs leading-6 text-[var(--admin-muted)]">فایل جدید را وارد کتابخانه کن؛ تصاویر ثابت در همان فرم قابل کراپ‌اند.</span>
                </a>
            </div>
        </section>
    </div>
@endsection
