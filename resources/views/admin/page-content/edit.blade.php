@extends('admin.layouts.app')

@section('title', 'محتوای صفحات عمومی')
@section('page_title', 'CMS صفحات عمومی')

@section('content')
    <div class="space-y-6" data-page-content-cms>
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-[var(--admin-accent)]">FARZIN / CONTENT MANAGEMENT</p>
                <h1 class="admin-title mt-2">محتوا و ظاهر صفحات عمومی</h1>
                <p class="admin-subtitle mt-2 max-w-3xl">متن‌های عمومی، نمایش بخش‌ها و تایپوگرافی را از اینجا کنترل کنید. تغییرات پس از ذخیره از همان داده‌های ذخیره‌شده در صفحات عمومی خوانده می‌شوند.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-secondary">مشاهده سایت</a>
                @php
                    $previewUrl = match ($tab) {
                        'about' => route('about'),
                        'legal' => route('terms'),
                        default => route('home'),
                    };
                @endphp
                <a href="{{ $previewUrl }}" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-primary">پیش‌نمایش تب جاری</a>
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

        <nav class="grid grid-cols-2 gap-2 sm:grid-cols-4" aria-label="تب‌های مدیریت محتوا">
            @foreach($groups as $key => $item)
                <a href="{{ route('admin.page-content.edit', ['tab' => $key]) }}"
                   @class([
                       'rounded-2xl border px-4 py-4 text-sm font-semibold transition',
                       'border-[var(--admin-accent)] bg-[var(--admin-accent-soft)] text-[var(--admin-accent-dark)]' => $tab === $key,
                       'border-[var(--admin-border)] bg-[var(--admin-card)] text-[var(--admin-text-soft)] hover:bg-[var(--admin-surface)]' => $tab !== $key,
                   ])
                   @if($tab === $key) aria-current="page" @endif>
                    <span class="block">{{ $item['title'] }}</span>
                    <span class="mt-1 block text-[10px] font-normal opacity-70">{{ count($item['fields']) }} تنظیم</span>
                </a>
            @endforeach
        </nav>

        <section class="admin-card overflow-hidden">
            <div class="border-b border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-5 py-5 sm:px-7">
                <h2 class="text-base font-bold text-[var(--admin-text)]">{{ $group['title'] }}</h2>
                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">{{ $group['description'] }}</p>
            </div>

            <form method="POST" action="{{ route('admin.page-content.update') }}" enctype="multipart/form-data" class="space-y-6 p-5 sm:p-7">
                @csrf
                @method('PUT')
                <input type="hidden" name="tab" value="{{ $tab }}">

                @if($tab === 'home')
                    @php
                        $publicMediaDisk = \Illuminate\Support\Facades\Storage::disk('public');
                        $heroImageValue = trim((string) ($values['page_home_hero_image_url'] ?? ''));
                        $heroImagePreview = '';
                        if ($heroImageValue !== '') {
                            if (\Illuminate\Support\Str::startsWith($heroImageValue, ['http://', 'https://', '//'])) {
                                $heroImagePreview = $heroImageValue;
                            } else {
                                $heroImagePath = preg_replace('#^(public/|storage/)#', '', ltrim($heroImageValue, '/')) ?? $heroImageValue;
                                $heroImagePreview = $publicMediaDisk->exists($heroImagePath)
                                    ? $publicMediaDisk->url($heroImagePath)
                                    : '';
                            }
                        }

                        $heroTitlePreview = trim((string) old(
                            'page_home_hero_title',
                            $values['page_home_hero_title'] ?? ''
                        )) ?: 'هود و سینک، آماده‌ی نصب در آشپزخانه‌ی شما';

                        $heroDescriptionPreview = trim((string) old(
                            'page_home_hero_description',
                            $values['page_home_hero_description'] ?? ''
                        )) ?: 'مدل‌های موجود فرزین را با تصویر، قیمت و مشخصات واقعی بررسی کنید.';

                        $heroButtonPreview = trim((string) old(
                            'page_home_hero_primary_label',
                            $values['page_home_hero_primary_label'] ?? ''
                        )) ?: 'مشاهده محصولات';
                    @endphp

                    <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4 text-xs leading-6 text-[var(--admin-muted)]">
                        این پیش‌نمایش همان نسبت تصویر و همان کارت پیشنهاد صفحه اصلی را نشان می‌دهد. فایل انتخابی با نسبت ثابت ۱۶:۱۰ برش می‌خورد؛ تصویر نهایی دقیقاً با همان قاب در Home استفاده می‌شود.
                    </div>

                    <section class="fz-admin-home-hero-preview" data-home-hero-preview aria-labelledby="fz-admin-home-preview-title">
                        <div class="fz-admin-home-hero-preview__toolbar">
                            <div>
                                <h3 id="fz-admin-home-preview-title">پیش‌نمایش واقعی Hero صفحه اصلی</h3>
                                <p>نمایش دسکتاپ؛ در موبایل، کارت پیشنهاد زیر تصویر قرار می‌گیرد.</p>
                            </div>
                            <span>نسبت تصویر ۱۶:۱۰</span>
                        </div>

                        <div class="fz-admin-home-hero-preview__layout">
                            <div class="fz-admin-home-hero-preview__copy">
                                <p class="fz-admin-home-hero-preview__eyebrow">تجهیزات آشپزخانه فرزین</p>
                                <h2 data-home-hero-preview-title>{{ $heroTitlePreview }}</h2>
                                <p data-home-hero-preview-description>{{ $heroDescriptionPreview }}</p>
                                <span class="fz-admin-home-hero-preview__button" data-home-hero-preview-cta>{{ $heroButtonPreview }} <span aria-hidden="true">←</span></span>
                            </div>

                            <div class="fz-admin-home-hero-preview__visual">
                                <div class="admin-image-preview fz-admin-home-hero-preview__saved {{ $heroImagePreview === '' ? 'hidden' : '' }}">
                                    <img
                                        data-image-editor-existing-preview
                                        @if($heroImagePreview !== '') src="{{ $heroImagePreview }}" @endif
                                        alt="تصویر فعلی Hero"
                                        width="1600"
                                        height="1000"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                    @if($heroProductPreview)
                                        <x-home.hero-offer :product="$heroProductPreview" />
                                    @else
                                        <div class="fz-admin-home-hero-preview__no-offer">فعلاً محصول موجود و منتخب برای کارت پیشنهاد ثبت نشده است.</div>
                                    @endif
                                </div>

                                <div data-image-editor-placeholder class="fz-admin-home-hero-preview__placeholder {{ $heroImagePreview !== '' ? 'hidden' : '' }}">
                                    <span aria-hidden="true">۱۶:۱۰</span>
                                    <strong>تصویر واقعی آشپزخانه اینجا نمایش داده می‌شود</strong>
                                    <small>بعد از انتخاب فایل، کادر قابل برش و جابه‌جایی می‌شود.</small>
                                </div>

                                <div data-image-editor-panel data-image-editor-panel-for="page_home_hero_image" hidden class="fz-admin-home-hero-crop-panel">
                                    <div class="fz-admin-home-hero-crop-frame">
                                        <canvas
                                            data-image-editor-canvas
                                            role="img"
                                            aria-label="پیش‌نمایش زنده برش تصویر Hero با کارت پیشنهاد فرزین"
                                        ></canvas>
                                        @if($heroProductPreview)
                                            <x-home.hero-offer :product="$heroProductPreview" />
                                        @else
                                            <div class="fz-admin-home-hero-preview__no-offer">کارت پیشنهاد فقط وقتی نمایش داده می‌شود که محصول منتخب و موجود باشد.</div>
                                        @endif
                                    </div>

                                    <div class="fz-admin-home-hero-crop-controls">
                                        <div class="fz-admin-home-hero-crop-controls__heading">
                                            <strong>تنظیم کادر نهایی تصویر</strong>
                                            <span>همان کادری که در صفحه اصلی منتشر می‌شود</span>
                                        </div>
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <label class="block text-xs text-[var(--admin-text)]">
                                                نسبت تصویر
                                                <select data-image-editor-ratio class="admin-select mt-2 w-full">
                                                    <option value="16:10" selected>۱۶:۱۰ — قاب ثابت Hero</option>
                                                </select>
                                            </label>
                                            <label class="block text-xs text-[var(--admin-text)]">
                                                بزرگ‌نمایی <span data-image-editor-zoom-label>۱×</span>
                                                <input data-image-editor-zoom type="range" min="1" max="3" step="0.05" value="1" class="mt-3 w-full">
                                            </label>
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" data-image-editor-rotate="-90" class="admin-btn admin-btn-secondary">چرخش به چپ</button>
                                            <button type="button" data-image-editor-rotate="90" class="admin-btn admin-btn-secondary">چرخش به راست</button>
                                            <button type="button" data-image-editor-reset class="admin-btn admin-btn-ghost">بازنشانی کادر</button>
                                        </div>
                                        <p data-image-editor-status class="text-xs leading-6 text-[var(--admin-muted)]" aria-live="polite">تصویر را با ماوس یا لمس بکشید تا سوژه در جای درست قرار بگیرد.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="fz-admin-home-hero-preview__upload">
                            <div>
                                <label for="page_home_hero_image" class="admin-label">انتخاب عکس اصلی آشپزخانه</label>
                                <p>JPG، PNG یا WEBP؛ حداقل ۱۲۰۰×۷۵۰ پیکسل و حداکثر ۲۰ مگابایت.</p>
                            </div>
                            <input
                                id="page_home_hero_image"
                                name="page_home_hero_image"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                data-image-editor-input
                                data-image-editor-default-ratio="16:10"
                                data-image-editor-min-width="1200"
                                data-image-editor-min-height="750"
                                class="admin-input w-full p-2"
                            >
                            @error('page_home_hero_image')
                                <p role="alert" class="text-xs text-[var(--admin-danger)]">{{ $message }}</p>
                            @enderror
                        </div>
                    </section>

                    <section class="space-y-4" aria-labelledby="fz-home-banners-admin-title">
                        <div>
                            <h3 id="fz-home-banners-admin-title" class="text-sm font-bold text-[var(--admin-text)]">اسلایدر بنرهای اصلی</h3>
                            <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">تا چهار بنر واقعی ثبت کنید. هر اسلاید فقط وقتی در سایت نمایش داده می‌شود که تصویر و عنوان ذخیره شده داشته باشد. تصویرها حداقل ۱۲۰۰×۳۷۵ و نسبت ۱۶:۶ باشند؛ متن هر اسلاید را در فیلدهای همین صفحه وارد کنید.</p>
                        </div>
                        <div class="grid gap-4 xl:grid-cols-2">
                            @php
                                $bannerImagePath1 = trim((string) ($values['page_home_banner_1_image_url'] ?? ''));
                                $bannerImageUrl1 = $bannerImagePath1 !== '' && $publicMediaDisk->exists($bannerImagePath1)
                                    ? $publicMediaDisk->url($bannerImagePath1)
                                    : '';
                            @endphp
                            <article class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="text-sm font-bold text-[var(--admin-text)]">بنر 1</h4>
                                    @if($bannerImageUrl1 !== '')
                                        <span class="text-[10px] font-semibold text-emerald-700">تصویر ذخیره‌شده</span>
                                    @else
                                        <span class="text-[10px] text-[var(--admin-muted)]">بدون تصویر</span>
                                    @endif
                                </div>
                                @if($bannerImageUrl1 !== '')
                                    <img src="{{ $bannerImageUrl1 }}" alt="پیش‌نمایش بنر 1" width="1200" height="450" loading="lazy" decoding="async" class="mt-3 aspect-[16/6] w-full rounded-xl object-cover">
                                @endif
                                <label for="page_home_banner_1_image" class="admin-label mt-4 block">تصویر بنر 1</label>
                                <input id="page_home_banner_1_image" name="page_home_banner_1_image" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input mt-2 w-full p-2">
                                <p class="mt-1 text-[10px] leading-5 text-[var(--admin-muted)]">JPG، PNG یا WEBP؛ نسبت ۱۶:۶؛ حداقل ۱۲۰۰×۳۷۵؛ حداکثر ۲۰ مگابایت.</p>
                                @error('page_home_banner_1_image')
                                    <p role="alert" class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>
                                @enderror
                                @if($bannerImageUrl1 !== '')
                                    <label class="mt-3 flex items-center gap-2 text-xs text-[var(--admin-text)]">
                                        <input type="checkbox" name="remove_page_home_banner_1_image" value="1" class="admin-checkbox h-4 w-4">
                                        حذف تصویر فعلی بنر 1
                                    </label>
                                @endif
                            </article>
                            @php
                                $bannerImagePath2 = trim((string) ($values['page_home_banner_2_image_url'] ?? ''));
                                $bannerImageUrl2 = $bannerImagePath2 !== '' && $publicMediaDisk->exists($bannerImagePath2)
                                    ? $publicMediaDisk->url($bannerImagePath2)
                                    : '';
                            @endphp
                            <article class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="text-sm font-bold text-[var(--admin-text)]">بنر 2</h4>
                                    @if($bannerImageUrl2 !== '')
                                        <span class="text-[10px] font-semibold text-emerald-700">تصویر ذخیره‌شده</span>
                                    @else
                                        <span class="text-[10px] text-[var(--admin-muted)]">بدون تصویر</span>
                                    @endif
                                </div>
                                @if($bannerImageUrl2 !== '')
                                    <img src="{{ $bannerImageUrl2 }}" alt="پیش‌نمایش بنر 2" width="1200" height="450" loading="lazy" decoding="async" class="mt-3 aspect-[16/6] w-full rounded-xl object-cover">
                                @endif
                                <label for="page_home_banner_2_image" class="admin-label mt-4 block">تصویر بنر 2</label>
                                <input id="page_home_banner_2_image" name="page_home_banner_2_image" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input mt-2 w-full p-2">
                                <p class="mt-1 text-[10px] leading-5 text-[var(--admin-muted)]">JPG، PNG یا WEBP؛ نسبت ۱۶:۶؛ حداقل ۱۲۰۰×۳۷۵؛ حداکثر ۲۰ مگابایت.</p>
                                @error('page_home_banner_2_image')
                                    <p role="alert" class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>
                                @enderror
                                @if($bannerImageUrl2 !== '')
                                    <label class="mt-3 flex items-center gap-2 text-xs text-[var(--admin-text)]">
                                        <input type="checkbox" name="remove_page_home_banner_2_image" value="1" class="admin-checkbox h-4 w-4">
                                        حذف تصویر فعلی بنر 2
                                    </label>
                                @endif
                            </article>
                            @php
                                $bannerImagePath3 = trim((string) ($values['page_home_banner_3_image_url'] ?? ''));
                                $bannerImageUrl3 = $bannerImagePath3 !== '' && $publicMediaDisk->exists($bannerImagePath3)
                                    ? $publicMediaDisk->url($bannerImagePath3)
                                    : '';
                            @endphp
                            <article class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="text-sm font-bold text-[var(--admin-text)]">بنر 3</h4>
                                    @if($bannerImageUrl3 !== '')
                                        <span class="text-[10px] font-semibold text-emerald-700">تصویر ذخیره‌شده</span>
                                    @else
                                        <span class="text-[10px] text-[var(--admin-muted)]">بدون تصویر</span>
                                    @endif
                                </div>
                                @if($bannerImageUrl3 !== '')
                                    <img src="{{ $bannerImageUrl3 }}" alt="پیش‌نمایش بنر 3" width="1200" height="450" loading="lazy" decoding="async" class="mt-3 aspect-[16/6] w-full rounded-xl object-cover">
                                @endif
                                <label for="page_home_banner_3_image" class="admin-label mt-4 block">تصویر بنر 3</label>
                                <input id="page_home_banner_3_image" name="page_home_banner_3_image" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input mt-2 w-full p-2">
                                <p class="mt-1 text-[10px] leading-5 text-[var(--admin-muted)]">JPG، PNG یا WEBP؛ نسبت ۱۶:۶؛ حداقل ۱۲۰۰×۳۷۵؛ حداکثر ۲۰ مگابایت.</p>
                                @error('page_home_banner_3_image')
                                    <p role="alert" class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>
                                @enderror
                                @if($bannerImageUrl3 !== '')
                                    <label class="mt-3 flex items-center gap-2 text-xs text-[var(--admin-text)]">
                                        <input type="checkbox" name="remove_page_home_banner_3_image" value="1" class="admin-checkbox h-4 w-4">
                                        حذف تصویر فعلی بنر 3
                                    </label>
                                @endif
                            </article>
                            @php
                                $bannerImagePath4 = trim((string) ($values['page_home_banner_4_image_url'] ?? ''));
                                $bannerImageUrl4 = $bannerImagePath4 !== '' && $publicMediaDisk->exists($bannerImagePath4)
                                    ? $publicMediaDisk->url($bannerImagePath4)
                                    : '';
                            @endphp
                            <article class="rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="text-sm font-bold text-[var(--admin-text)]">بنر 4</h4>
                                    @if($bannerImageUrl4 !== '')
                                        <span class="text-[10px] font-semibold text-emerald-700">تصویر ذخیره‌شده</span>
                                    @else
                                        <span class="text-[10px] text-[var(--admin-muted)]">بدون تصویر</span>
                                    @endif
                                </div>
                                @if($bannerImageUrl4 !== '')
                                    <img src="{{ $bannerImageUrl4 }}" alt="پیش‌نمایش بنر 4" width="1200" height="450" loading="lazy" decoding="async" class="mt-3 aspect-[16/6] w-full rounded-xl object-cover">
                                @endif
                                <label for="page_home_banner_4_image" class="admin-label mt-4 block">تصویر بنر 4</label>
                                <input id="page_home_banner_4_image" name="page_home_banner_4_image" type="file" accept="image/jpeg,image/png,image/webp" class="admin-input mt-2 w-full p-2">
                                <p class="mt-1 text-[10px] leading-5 text-[var(--admin-muted)]">JPG، PNG یا WEBP؛ نسبت ۱۶:۶؛ حداقل ۱۲۰۰×۳۷۵؛ حداکثر ۲۰ مگابایت.</p>
                                @error('page_home_banner_4_image')
                                    <p role="alert" class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>
                                @enderror
                                @if($bannerImageUrl4 !== '')
                                    <label class="mt-3 flex items-center gap-2 text-xs text-[var(--admin-text)]">
                                        <input type="checkbox" name="remove_page_home_banner_4_image" value="1" class="admin-checkbox h-4 w-4">
                                        حذف تصویر فعلی بنر 4
                                    </label>
                                @endif
                            </article>
                        </div>
                    </section>

                    @php
                        $technologyImagePath = trim((string) ($values['page_home_technology_image_url'] ?? ''));
                        if ($technologyImagePath === '') {
                            $technologyImagePreview = '';
                        } elseif (\Illuminate\Support\Str::startsWith($technologyImagePath, ['http://', 'https://', '//'])) {
                            $technologyImagePreview = $technologyImagePath;
                        } else {
                            $technologyImagePath = preg_replace('#^(public/|storage/)#', '', ltrim($technologyImagePath, '/')) ?? $technologyImagePath;
                            $technologyImagePreview = $publicMediaDisk->exists($technologyImagePath)
                                ? $publicMediaDisk->url($technologyImagePath)
                                : '';
                        }
                    @endphp
                    <section class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4 sm:p-5" aria-labelledby="home-technology-image-title">
                        <div class="mb-4">
                            <h3 id="home-technology-image-title" class="text-sm font-bold text-[var(--admin-text)]">تصویر سکشن فناوری و جزئیات ساخت</h3>
                            <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">این تصویر در صفحه اصلی با قاب ثابت ۱۶:۱۰ نمایش داده می‌شود. برای معرفی واقعی فضای تولید، جزئیات فنی یا محصول از عکس مجموعه استفاده کنید.</p>
                        </div>
                        <div class="admin-image-preview mb-4 aspect-[16/10] w-full max-w-2xl">
                            <img src="{{ $technologyImagePreview }}" alt="پیش‌نمایش تصویر سکشن فناوری" width="1200" height="750" loading="lazy" decoding="async" data-image-editor-existing-preview class="{{ $technologyImagePreview !== '' ? '' : 'hidden' }}">
                            <div data-image-editor-placeholder class="{{ $technologyImagePreview !== '' ? 'hidden' : 'flex' }} h-full flex-col items-center justify-center gap-2 bg-[var(--admin-card)] p-5 text-center text-xs text-[var(--admin-muted)]">هنوز تصویری برای سکشن فناوری ثبت نشده است.</div>
                        </div>
                        <label for="page_home_technology_image" class="admin-label">بارگذاری تصویر ۱۶:۱۰</label>
                        <input id="page_home_technology_image" name="page_home_technology_image" type="file" data-image-editor-input data-image-editor-default-ratio="16:10" data-image-editor-min-width="1200" data-image-editor-min-height="750" accept="image/jpeg,image/png,image/webp" class="admin-input mt-2 w-full max-w-2xl p-2"> 
                        <div data-image-editor-panel-for="page_home_technology_image" hidden class="mt-5 max-w-2xl space-y-4 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-card)] p-4">
                            <strong class="block text-xs font-bold text-[var(--admin-text)]">برش و پیش‌نمایش تصویر صفحه اصلی</strong>
                            <canvas data-image-editor-canvas role="img" aria-label="پیش‌نمایش زنده برش تصویر فناوری" class="mx-auto block w-full rounded-lg bg-[var(--admin-bg)]" style="height:auto;aspect-ratio:16/10;touch-action:none;cursor:grab;"></canvas>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <label class="text-xs text-[var(--admin-text)]">نسبت کادر
                                    <select data-image-editor-ratio class="admin-select mt-2 w-full"><option value="16:10" selected>۱۶:۱۰</option><option value="4:3">۴:۳</option><option value="1:1">۱:۱</option></select>
                                </label>
                                <label class="text-xs text-[var(--admin-text)]">بزرگ‌نمایی <span data-image-editor-zoom-label>۱×</span>
                                    <input data-image-editor-zoom type="range" min="1" max="3" step="0.05" value="1" class="mt-3 w-full">
                                </label>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" data-image-editor-rotate="-90" class="admin-btn admin-btn-secondary">چرخش به چپ</button>
                                <button type="button" data-image-editor-rotate="90" class="admin-btn admin-btn-secondary">چرخش به راست</button>
                                <button type="button" data-image-editor-reset class="admin-btn admin-btn-ghost">بازنشانی</button>
                            </div>
                            <p data-image-editor-status class="text-xs text-[var(--admin-muted)]" aria-live="polite">کادر تصویر را تنظیم کنید.</p>
                        </div>
                        <p class="mt-1 text-[10px] leading-5 text-[var(--admin-muted)]">JPG، PNG یا WEBP؛ حداقل ۱۲۰۰×۷۵۰ و دقیقاً نسبت ۱۶:۱۰؛ حداکثر ۲۰ مگابایت. فایل قبلی تا ذخیره موفق تصویر جدید باقی می‌ماند.</p>
                        @error('page_home_technology_image')
                            <p role="alert" class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>
                        @enderror
                    </section>
                @elseif($tab === 'about')
                    <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-4 text-xs leading-6 text-[var(--admin-muted)]">
                        اطلاعات فنی و ادعاهای تجاری را فقط با داده تأییدشده مجموعه جایگزین کنید. این فیلدها متن ساده‌اند و HTML یا اسکریپت اجرا نمی‌کنند.
                    </div>
                @elseif($tab === 'legal')
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs leading-6 text-amber-900">
                        متن قوانین و حریم خصوصی بهتر است پیش از انتشار توسط مسئول حقوقی/کسب‌وکار تأیید شود. متن‌ها به‌صورت امن و بدون اجرای HTML نمایش داده می‌شوند.
                    </div>
                @endif

                <div class="grid gap-5 xl:grid-cols-2">
                    @foreach($group['fields'] as $key => $field)
                        @php
                            $value = old($key, $values[$key] ?? '');
                            $isWide = ($field['type'] ?? '') === 'textarea';
                        @endphp
                        <div @class(['min-w-0', 'xl:col-span-2' => $isWide])>
                            @if($field['type'] === 'toggle')
                                <label class="flex min-h-14 cursor-pointer items-center justify-between gap-4 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] px-4 py-3">
                                    <span class="text-sm font-semibold text-[var(--admin-text)]">{{ $field['label'] }}</span>
                                    <span class="flex items-center gap-2">
                                        <span class="text-[10px] text-[var(--admin-muted)]">نمایش</span>
                                        <input type="hidden" name="{{ $key }}" value="0">
                                        <input type="checkbox" name="{{ $key }}" value="1" @checked((string) $value === '1') class="admin-checkbox h-4 w-4">
                                    </span>
                                </label>
                            @else
                                <label for="{{ $key }}" class="admin-label">{{ $field['label'] }}</label>
                                @if($field['type'] === 'textarea')
                                    <textarea id="{{ $key }}" name="{{ $key }}" rows="3" maxlength="{{ $field['max'] }}" class="admin-textarea mt-2 w-full">{{ $value }}</textarea>
                                @elseif($field['type'] === 'select')
                                    <select id="{{ $key }}" name="{{ $key }}" class="admin-select mt-2 w-full">
                                        @foreach($field['options'] as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input id="{{ $key }}" name="{{ $key }}" type="{{ $field['type'] === 'number' ? 'number' : 'text' }}"
                                           value="{{ $value }}" @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                                           @if(isset($field['max']) && $field['type'] === 'number') max="{{ $field['max'] }}" @elseif(isset($field['max'])) maxlength="{{ $field['max'] }}" @endif
                                           @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                                           class="admin-input mt-2 w-full @if($field['type'] === 'number') text-left @endif"
                                           @if($field['type'] === 'number') dir="ltr" inputmode="decimal" @endif>
                                @endif
                            @endif
                            @error($key)<p role="alert" class="mt-2 text-xs text-[var(--admin-danger)]">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col gap-3 border-t border-[var(--admin-border)] pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs leading-6 text-[var(--admin-muted)]">ذخیره هر تب فقط تنظیمات همان تب را به‌روزرسانی می‌کند؛ سایر محتواها دست‌نخورده می‌مانند.</p>
                    <button type="submit" class="admin-btn admin-btn-primary min-w-36">ذخیره تغییرات</button>
                </div>
            </form>
        </section>
    </div>
@endsection
